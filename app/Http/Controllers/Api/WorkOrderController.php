<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkOrderResource;
use App\Models\ScheduleDay;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class WorkOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = WorkOrder::query()->with([
            'scheduleDay.supervisorOnCall',
            'building.managementCompany',
            'building.zone',
            'building.bcSupervisor',
            'subcontractor',
            'subcontractor.employees',
            'pendingJob',
        ]);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $b) use ($search): void {
                $b->where('job_description', 'like', "%{$search}%")
                    ->orWhere('unit_area', 'like', "%{$search}%")
                    ->orWhere('request_po_wtn_wo', 'like', "%{$search}%")
                    ->orWhere('bc_work_order', 'like', "%{$search}%")
                    ->orWhere('bc_estimate_ref', 'like', "%{$search}%")
                    ->orWhere('special_notes', 'like', "%{$search}%");
            });
        }

        if ($from = $request->query('from')) {
            $query->whereDate('day_id', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('day_id', '<=', $to);
        }

        foreach (['day_id', 'building_id', 'subcontractor_id', 'worksite_status'] as $col) {
            if ($request->filled($col)) {
                $query->where($col, $request->query($col));
            }
        }

        if ($request->filled('management_id')) {
            $query->whereHas('building', fn (Builder $b) => $b->where('management_id', $request->query('management_id')));
        }

        if ($request->filled('zone_id')) {
            $query->whereHas('building', fn (Builder $b) => $b->where('zone_id', $request->query('zone_id')));
        }

        $perPage = min((int) $request->query('per_page', 25), 200);

        return WorkOrderResource::collection(
            $query->orderByDesc('day_id')->orderByDesc('job_id')->paginate($perPage)
        );
    }

    public function statuses(): JsonResponse
    {
        return response()->json(['data' => WorkOrder::worksiteStatuses()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $this->ensureScheduleDayExists($data['day_id']);

        $order = WorkOrder::query()->create($data);

        return (new WorkOrderResource(
            $order->load(['scheduleDay.supervisorOnCall', 'building.managementCompany', 'building.zone', 'subcontractor.employees', 'pendingJob'])
        ))->response()->setStatusCode(201);
    }

    public function show(WorkOrder $workOrder): WorkOrderResource
    {
        return new WorkOrderResource(
            $workOrder->load(['scheduleDay.supervisorOnCall', 'building.managementCompany', 'building.zone', 'subcontractor.employees', 'pendingJob'])
        );
    }

    public function update(Request $request, WorkOrder $workOrder): WorkOrderResource
    {
        $data = $this->validated($request);

        if (isset($data['day_id'])) {
            $this->ensureScheduleDayExists($data['day_id']);
        }

        $workOrder->update($data);

        return new WorkOrderResource(
            $workOrder->fresh()->load(['scheduleDay.supervisorOnCall', 'building.managementCompany', 'building.zone', 'subcontractor.employees', 'pendingJob'])
        );
    }

    public function destroy(WorkOrder $workOrder): JsonResponse
    {
        $workOrder->delete();

        return response()->json(null, 204);
    }

    private function ensureScheduleDayExists(string $day): void
    {
        ScheduleDay::query()->firstOrCreate(
            ['day_id' => $day],
            ['day_of_week' => Carbon::parse($day)->format('l')],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'day_id' => ['required', 'date'],
            'building_id' => ['nullable', Rule::exists('buildings', 'building_id')],
            'subcontractor_id' => ['nullable', Rule::exists('subcontractors', 'subcontractor_id')],
            'unit_area' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:64'],
            'worksite_status' => ['nullable', 'string', 'max:64'],
            'job_description' => ['nullable', 'string'],
            'request_po_wtn_wo' => ['nullable', 'string', 'max:255'],
            'bc_work_order' => ['nullable', 'string', 'max:255'],
            'bc_estimate_ref' => ['nullable', 'string', 'max:255'],
            'extras' => ['nullable', 'string'],
            'special_notes' => ['nullable', 'string'],
            'vendor_status_report' => ['nullable', 'string'],
            'assigned_employee_ids' => ['nullable', 'array'],
            'assigned_employee_ids.*' => ['integer', Rule::exists('subcontractor_employees', 'employee_id')],
        ]);
    }
}
