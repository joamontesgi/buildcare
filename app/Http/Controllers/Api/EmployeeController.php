<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Employee::query();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $b) use ($search): void {
                $b->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return EmployeeResource::collection(
            $query->orderBy('first_name')->orderBy('last_name')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $employee = Employee::query()->create($this->validated($request));

        return (new EmployeeResource($employee))->response()->setStatusCode(201);
    }

    public function show(Employee $employee): EmployeeResource
    {
        return new EmployeeResource($employee);
    }

    public function update(Request $request, Employee $employee): EmployeeResource
    {
        $employee->update($this->validated($request, $employee->id));

        return new EmployeeResource($employee->fresh());
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($id)],
        ]);
    }
}
