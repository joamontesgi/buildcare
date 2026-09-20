<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubcontractorResource;
use App\Models\Subcontractor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class SubcontractorController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Subcontractor::query()->withCount('employees');

        if ($request->boolean('with_employees')) {
            $query->with('employees');
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $b) use ($search): void {
                $b->where('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('capabilities', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return SubcontractorResource::collection(
            $query->orderBy('company_name')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $sub = Subcontractor::query()->create($this->validated($request));

        return (new SubcontractorResource($sub->loadCount('employees')))
            ->response()->setStatusCode(201);
    }

    public function show(Subcontractor $subcontractor): SubcontractorResource
    {
        return new SubcontractorResource(
            $subcontractor->load('employees')->loadCount('employees')
        );
    }

    public function update(Request $request, Subcontractor $subcontractor): SubcontractorResource
    {
        $subcontractor->update($this->validated($request, $subcontractor->subcontractor_id));

        return new SubcontractorResource(
            $subcontractor->fresh()->load('employees')->loadCount('employees')
        );
    }

    public function destroy(Subcontractor $subcontractor): JsonResponse
    {
        $subcontractor->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255', Rule::unique('subcontractors', 'company_name')->ignore($id, 'subcontractor_id')],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'capabilities' => ['nullable', 'string'],
        ]);
    }
}
