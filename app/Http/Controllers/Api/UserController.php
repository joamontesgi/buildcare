<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load('roleModel'));
    }

    public function roles(): JsonResponse
    {
        return response()->json(['data' => User::roles()]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query()->with('roleModel');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhereHas('roleModel', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
            });
        }

        if ($roleSlug = trim((string) $request->query('role', ''))) {
            $query->where(function ($b) use ($roleSlug): void {
                $b->where('role', $roleSlug)
                    ->orWhereHas('roleModel', fn ($q) => $q->where('slug', $roleSlug));
            });
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return UserResource::collection(
            $query->orderBy('name')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $user = User::query()->create($this->validated($request));
        $user->load('roleModel');

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function update(Request $request, User $user): UserResource
    {
        $data = $this->validated($request, $user);
        $user->update($data);

        return new UserResource($user->fresh()->load('roleModel'));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()?->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        // Debe quedar al menos un admin
        $adminSlug = Role::SLUG_ADMIN;
        $isLastAdmin = $user->isAdmin() &&
            User::query()->where(function ($b) use ($adminSlug): void {
                $b->where('role', $adminSlug)
                    ->orWhereHas('roleModel', fn ($q) => $q->where('slug', $adminSlug));
            })->count() <= 1;

        if ($isLastAdmin) {
            return response()->json(['message' => 'At least one administrator must remain.'], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $user = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            // Se acepta role_id (nuevo) o role slug (compatibilidad).
            'role_id' => ['nullable', 'integer', Rule::exists('roles', 'role_id')],
            'role' => ['nullable', 'string', 'max:64'],
        ];

        if ($user) {
            $rules['password'] = ['nullable', 'string', Password::defaults()];
        } else {
            $rules['password'] = ['required', 'string', Password::defaults()];
        }

        $data = $request->validate($rules);

        // Reglas de negocio: al menos uno de role_id o role slug debe existir.
        if (! isset($data['role_id']) && empty($data['role'])) {
            abort(422, 'Debes seleccionar un rol.');
        }

        // Si viene role_id, sincronizamos slug para compatibilidad legacy.
        if (! empty($data['role_id'])) {
            $role = Role::query()->find($data['role_id']);
            if ($role) {
                $data['role'] = $role->slug;
            }
        } elseif (! empty($data['role'])) {
            // Si vino solo slug, intentar resolver a role_id.
            $roleId = Role::query()->where('slug', $data['role'])->value('role_id');
            $data['role_id'] = $roleId;
        }

        if ($user && empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
