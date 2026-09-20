<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Role::query()->withCount('users');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($b) use ($search): void {
                $b->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 50), 100);

        return RoleResource::collection(
            $query->orderBy('role_id')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $role = Role::query()->create($this->validated($request));

        return (new RoleResource($role))->response()->setStatusCode(201);
    }

    public function show(Role $role): RoleResource
    {
        return new RoleResource($role->loadCount('users'));
    }

    public function update(Request $request, Role $role): RoleResource
    {
        $role->update($this->validated($request, $role->role_id));

        return new RoleResource($role->fresh()->loadCount('users'));
    }

    public function destroy(Role $role): JsonResponse
    {
        // Protege slugs canónicos del negocio.
        $canonical = collect(Role::catalog())->pluck('slug')->all();
        if (in_array($role->slug, $canonical, true)) {
            return response()->json([
                'message' => 'Canonical catalog roles cannot be deleted.',
            ], 422);
        }

        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a role that is assigned to users.',
            ], 422);
        }

        $role->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_\-]+$/', Rule::unique('roles', 'slug')->ignore($id, 'role_id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'can_manage_schedule' => ['sometimes', 'boolean'],
            'is_admin' => ['sometimes', 'boolean'],
        ]);
    }
}
