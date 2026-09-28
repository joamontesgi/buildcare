<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaffRoleResource;
use App\Models\StaffRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class StaffRoleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = StaffRole::query();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = min((int) $request->query('per_page', 50), 100);

        return StaffRoleResource::collection(
            $query->orderBy('name')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $role = StaffRole::query()->create($this->validated($request));

        return (new StaffRoleResource($role))->response()->setStatusCode(201);
    }

    public function show(StaffRole $staffRole): StaffRoleResource
    {
        return new StaffRoleResource($staffRole);
    }

    public function update(Request $request, StaffRole $staffRole): StaffRoleResource
    {
        $staffRole->update($this->validated($request, $staffRole->id));

        return new StaffRoleResource($staffRole->fresh());
    }

    public function destroy(StaffRole $staffRole): JsonResponse
    {
        $staffRole->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($id)],
        ]);
    }
}
