<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubcontractorEmployeeResource;
use App\Models\Subcontractor;
use App\Models\SubcontractorEmployee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubcontractorEmployeeController extends Controller
{
    // Rutas anidadas (subcontractors.employees) para index y store.
    public function index(Request $request, Subcontractor $subcontractor): AnonymousResourceCollection
    {
        $query = $subcontractor->employees()->getQuery();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($b) use ($search): void {
                $b->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        return SubcontractorEmployeeResource::collection(
            $query->orderBy('name')->paginate(50)
        );
    }

    public function store(Request $request, Subcontractor $subcontractor): JsonResponse
    {
        $employee = $subcontractor->employees()->create($this->validated($request));

        return (new SubcontractorEmployeeResource($employee))
            ->response()->setStatusCode(201);
    }

    // Rutas shallow (employees/{employee}) para show/update/destroy.
    public function show(SubcontractorEmployee $employee): SubcontractorEmployeeResource
    {
        return new SubcontractorEmployeeResource($employee);
    }

    public function update(Request $request, SubcontractorEmployee $employee): SubcontractorEmployeeResource
    {
        $employee->update($this->validated($request));

        return new SubcontractorEmployeeResource($employee->fresh());
    }

    public function destroy(SubcontractorEmployee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'role' => ['nullable', 'string', 'max:64'],
        ]);
    }
}
