<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkOrderResource;
use App\Models\WorkOrder;
use App\Models\WorkOrderReference;
use App\Models\WorkOrderStaff;
use App\Models\WorkOrderVendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = WorkOrder::query()->with([
            'property.state',
            'property.managementCompany',
            'property.billingClerk',
            'worksiteStatus',
            'jobStatus',
            'requestSource',
            'references',
            'staffAssignments.employee',
            'staffAssignments.staffRole',
            'workOrderVendors.vendor',
            'vendorDailyStatusReports.vendor',
        ]);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $b) use ($search): void {
                $b->where('job_description', 'like', "%{$search}%")
                    ->orWhere('unit_area', 'like', "%{$search}%")
                    ->orWhere('bc_work_order', 'like', "%{$search}%")
                    ->orWhere('bc_estimate', 'like', "%{$search}%")
                    ->orWhere('special_notes_sequence', 'like', "%{$search}%");
            });
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        foreach (['property_id', 'worksite_status_id', 'job_status_id', 'request_source_id'] as $col) {
            if ($request->filled($col)) {
                $query->where($col, $request->query($col));
            }
        }

        if ($request->filled('management_id')) {
            $query->whereHas('property', fn (Builder $b) => $b->where('management_id', $request->query('management_id')));
        }

        if ($request->filled('state_id')) {
            $query->whereHas('property', fn (Builder $b) => $b->where('state_id', $request->query('state_id')));
        }

        $perPage = min((int) $request->query('per_page', 25), 200);

        return WorkOrderResource::collection(
            $query->orderByDesc('created_at')->orderByDesc('id')->paginate($perPage)
        );
    }

    public function statuses(): JsonResponse
    {
        $statuses = \App\Models\WorksiteStatus::query()->orderBy('name')->pluck('name', 'id');

        return response()->json(['data' => $statuses]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $order = DB::transaction(function () use ($data) {
            $nested = $this->extractNested($data);
            $order = WorkOrder::query()->create($data);
            $this->syncNested($order, $nested);

            return $order;
        });

        return (new WorkOrderResource($this->loadRelations($order)))->response()->setStatusCode(201);
    }

    public function show(WorkOrder $workOrder): WorkOrderResource
    {
        return new WorkOrderResource($this->loadRelations($workOrder));
    }

    public function update(Request $request, WorkOrder $workOrder): WorkOrderResource
    {
        $data = $this->validated($request, $workOrder->id);

        DB::transaction(function () use ($workOrder, $data): void {
            $nested = $this->extractNested($data);
            $workOrder->update($data);
            if ($nested !== null) {
                $this->syncNested($workOrder, $nested);
            }
        });

        return new WorkOrderResource($this->loadRelations($workOrder->fresh()));
    }

    public function destroy(WorkOrder $workOrder): JsonResponse
    {
        $workOrder->delete();

        return response()->json(null, 204);
    }

    private function loadRelations(WorkOrder $order): WorkOrder
    {
        return $order->load([
            'property.state',
            'property.managementCompany',
            'property.billingClerk',
            'worksiteStatus',
            'jobStatus',
            'requestSource',
            'references',
            'staffAssignments.employee',
            'staffAssignments.staffRole',
            'workOrderVendors.vendor',
            'vendorDailyStatusReports.vendor',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private function extractNested(array &$data): ?array
    {
        $nested = [];
        foreach (['references', 'staff', 'vendor_ids'] as $key) {
            if (array_key_exists($key, $data)) {
                $nested[$key] = $data[$key];
                unset($data[$key]);
            }
        }

        return $nested === [] ? null : $nested;
    }

    /**
     * @param  array<string, mixed>  $nested
     */
    private function syncNested(WorkOrder $order, array $nested): void
    {
        if (array_key_exists('references', $nested)) {
            $order->references()->delete();
            foreach ($nested['references'] ?? [] as $ref) {
                WorkOrderReference::query()->create([
                    'work_order_id' => $order->id,
                    'reference_type' => $ref['reference_type'],
                    'reference_number' => $ref['reference_number'] ?? null,
                ]);
            }
        }

        if (array_key_exists('staff', $nested)) {
            $order->staffAssignments()->delete();
            foreach ($nested['staff'] ?? [] as $row) {
                WorkOrderStaff::query()->create([
                    'work_order_id' => $order->id,
                    'employee_id' => $row['employee_id'],
                    'role_id' => $row['role_id'],
                ]);
            }
        }

        if (array_key_exists('vendor_ids', $nested)) {
            $order->workOrderVendors()->delete();
            foreach ($nested['vendor_ids'] ?? [] as $vendorId) {
                WorkOrderVendor::query()->create([
                    'work_order_id' => $order->id,
                    'vendor_id' => $vendorId,
                ]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        $propertyRule = $id === null ? 'required' : 'sometimes';

        return $request->validate([
            'property_id' => [$propertyRule, Rule::exists('properties', 'id')],
            'unit_area' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:100'],
            'worksite_status_id' => ['nullable', Rule::exists('worksite_statuses', 'id')],
            'job_status_id' => ['nullable', Rule::exists('job_statuses', 'id')],
            'job_description' => ['nullable', 'string'],
            'bc_work_order' => ['nullable', 'string', 'max:100'],
            'bc_estimate' => ['nullable', 'string', 'max:100'],
            'extras' => ['nullable', 'string'],
            'special_notes_sequence' => ['nullable', 'string'],
            'request_source_id' => ['nullable', Rule::exists('request_sources', 'id')],
            'references' => ['nullable', 'array'],
            'references.*.reference_type' => ['required_with:references', 'string', 'max:30'],
            'references.*.reference_number' => ['nullable', 'string', 'max:100'],
            'staff' => ['nullable', 'array'],
            'staff.*.employee_id' => ['required_with:staff', Rule::exists('employees', 'id')],
            'staff.*.role_id' => ['required_with:staff', Rule::exists('roles', 'id')],
            'vendor_ids' => ['nullable', 'array'],
            'vendor_ids.*' => ['integer', Rule::exists('vendors', 'id')],
        ]);
    }
}
