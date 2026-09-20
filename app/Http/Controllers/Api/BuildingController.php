<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingResource;
use App\Models\Building;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class BuildingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Building::query()->with(['managementCompany', 'zone', 'bcSupervisor']);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $b) use ($search): void {
                $b->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhere('billing_clerk_code', 'like', "%{$search}%");
            });
        }

        foreach (['state', 'management_id', 'zone_id', 'bc_supervisor_id'] as $col) {
            if ($request->filled($col)) {
                $query->where($col, $request->query($col));
            }
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return BuildingResource::collection(
            $query->orderBy('name')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $building = Building::query()->create($this->validated($request));

        return (new BuildingResource(
            $building->load(['managementCompany', 'zone', 'bcSupervisor'])
        ))->response()->setStatusCode(201);
    }

    public function show(Building $building): BuildingResource
    {
        return new BuildingResource(
            $building->load(['managementCompany', 'zone', 'bcSupervisor'])
        );
    }

    public function update(Request $request, Building $building): BuildingResource
    {
        $building->update($this->validated($request));

        return new BuildingResource(
            $building->fresh()->load(['managementCompany', 'zone', 'bcSupervisor'])
        );
    }

    public function destroy(Building $building): JsonResponse
    {
        $building->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:32'],
            'management_id' => ['nullable', Rule::exists('management_companies', 'management_id')],
            'zone_id' => ['nullable', Rule::exists('zones', 'zone_id')],
            'bc_supervisor_id' => ['nullable', Rule::exists('staff', 'staff_id')],
            'billing_clerk_code' => ['nullable', 'string', 'max:64'],
            'travel_expense' => ['nullable', 'string', 'max:255'],
            'paint_specs' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
