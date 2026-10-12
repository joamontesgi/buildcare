<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Support\SiteOccupancyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Property::query()->with([
            'state' => fn ($relation) => $relation->withTrashed(),
            'managementCompany' => fn ($relation) => $relation->withTrashed(),
            'billingClerk' => fn ($relation) => $relation->withTrashed(),
            'propertyStaff.employee',
            'propertyStaff.staffRole',
        ]);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $b) use ($search): void {
                $b->where('building_name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('zip_code', 'like', "%{$search}%")
                    ->orWhereHas(
                        'managementCompany',
                        fn (Builder $management) => $management
                            ->withTrashed()
                            ->where('name', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'state',
                        fn (Builder $state) => $state
                            ->withTrashed()
                            ->where(function (Builder $stateQuery) use ($search): void {
                                $stateQuery->where('code', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                            })
                    )
                    ->orWhereHas(
                        'billingClerk',
                        fn (Builder $clerk) => $clerk
                            ->withTrashed()
                            ->where(function (Builder $clerkQuery) use ($search): void {
                                $clerkQuery->where('code', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                            })
                    );
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
            $property->load([
                'state',
                'managementCompany',
                'billingClerk',
                'propertyStaff.employee',
                'propertyStaff.staffRole',
            ])
        );
    }

    public function update(Request $request, Property $property): PropertyResource
    {
        $property->update($this->validated($request, $property->id));

        return new PropertyResource(
            $property->fresh()->load([
                'state',
                'managementCompany',
                'billingClerk',
                'propertyStaff.employee',
                'propertyStaff.staffRole',
            ])
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
            'city' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:20'],
            'state_id' => ['required', Rule::exists('states', 'id')],
            'management_id' => ['required', Rule::exists('management_companies', 'id')],
            'billing_clerk_id' => ['nullable', Rule::exists('billing_clerks', 'id')],
            'initial_site_status' => ['nullable', 'string', 'max:20', SiteOccupancyStatus::nullableRule()],
        ]);
    }
}
