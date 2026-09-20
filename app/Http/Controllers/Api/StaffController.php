<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaffResource;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Staff::query();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $b) use ($search): void {
                $b->where('name', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = trim((string) $request->query('role', ''))) {
            $query->where('role', $role);
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return StaffResource::collection(
            $query->orderBy('name')->paginate($perPage)
        );
    }

    public function roles(): JsonResponse
    {
        return response()->json(['data' => Staff::roles()]);
    }

    public function store(Request $request): JsonResponse
    {
        $staff = Staff::query()->create($this->validated($request));

        return (new StaffResource($staff))->response()->setStatusCode(201);
    }

    public function show(Staff $staff): StaffResource
    {
        return new StaffResource($staff);
    }

    public function update(Request $request, Staff $staff): StaffResource
    {
        $staff->update($this->validated($request, $staff->staff_id));

        return new StaffResource($staff->fresh());
    }

    public function destroy(Staff $staff): JsonResponse
    {
        $staff->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:64'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('staff', 'email')->ignore($id, 'staff_id')],
        ]);
    }
}
