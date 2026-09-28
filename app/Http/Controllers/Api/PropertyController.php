<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Property::query()->with(['state', 'managementCompany', 'billingClerk', 'propertyStaff.employee', 'propertyStaff.staffRole']);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $b) use ($search): void {
                $b->where('building_name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        foreach (['state_id', 'management_id', 'billing_clerk_id'] as $col) {
            if ($request->filled($col)) {
                $query->where($col, $request->query($col));
            }
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return PropertyResource::collection(
            $query->orderBy('building_name')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $property = Property::query()->create($this->validated($request));

        return (new PropertyResource(
            $property->load(['state', 'managementCompany', 'billingClerk'])
        ))->response()->setStatusCode(201);
    }

    public function show(Property $property): PropertyResource
    {
        return new PropertyResource(
            $property->load(['state', 'managementCompany', 'billingClerk', 'propertyStaff.employee', 'propertyStaff.staffRole'])
        );
    }

    public function update(Request $request, Property $property): PropertyResource
    {
        $property->update($this->validated($request, $property->id));

        return new PropertyResource(
            $property->fresh()->load(['state', 'managementCompany', 'billingClerk', 'propertyStaff.employee', 'propertyStaff.staffRole'])
        );
    }

    public function destroy(Property $property): JsonResponse
    {
        $property->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'building_name' => ['required', 'string', 'max:200'],
            'address' => ['required', 'string', 'max:300'],
            'state_id' => ['required', Rule::exists('states', 'id')],
            'management_id' => ['required', Rule::exists('management_companies', 'id')],
            'billing_clerk_id' => ['nullable', Rule::exists('billing_clerks', 'id')],
        ]);
    }
}
