<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use App\Http\Resources\WorkOrderResource;

use App\Models\Property;

use App\Models\WorkOrder;

use App\Models\WorkOrderReference;

use App\Models\WorkOrderStaff;

use App\Models\WorkOrderVendor;

use App\Support\SiteOccupancyStatus;
use App\Support\WorkOrderJobStatus;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;



class WorkOrderController extends Controller

{

    public function index(Request $request): AnonymousResourceCollection

    {

        $query = WorkOrder::query()->with([

            'property.state',

            'property.managementCompany',

            'property.billingClerk',

            'unitSize',

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

                    ->orWhere('order_code', 'like', "%{$search}%")

                    ->orWhereRaw('CAST(bc_estimate AS CHAR) LIKE ?', ["%{$search}%"])

                    ->orWhere('special_notes_sequence', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%");

            });

        }



        $executionFrom = $request->query('execution_date_from', $request->query('from'));

        $executionTo = $request->query('execution_date_to', $request->query('to'));

        if ($executionFrom) {

            $query->where('execution_date', '>=', $executionFrom);

        }

        if ($executionTo) {

            $query->where('execution_date', '<=', $executionTo);

        }



        foreach (['property_id', 'site_status', 'job_status_id', 'request_source_id'] as $col) {

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

            $query->orderedForDisplay()->paginate($perPage)

        );

    }



    public function statuses(): JsonResponse

    {

        return response()->json(['data' => SiteOccupancyStatus::options()]);

    }



    public function jobStatusOptions(): JsonResponse

    {

        return response()->json(['data' => WorkOrderJobStatus::transitionOptions()]);

    }



    public function updateJobStatus(Request $request, WorkOrder $workOrder): JsonResponse

    {

        $validated = $request->validate([

            'job_status' => ['required', 'string', WorkOrderJobStatus::transitionRule()],

            'execution_date' => [
                'required_if:job_status,'.WorkOrderJobStatus::CONTINUES,
                'required_if:job_status,'.WorkOrderJobStatus::RESCHEDULED,
                'date',
            ],

            'unit_area' => ['nullable', 'string', 'max:100'],

            'unit_size_id' => ['nullable', Rule::exists('unit_sizes', 'id')],

            'site_status' => ['nullable', 'string', 'max:20', SiteOccupancyStatus::nullableRule()],

            'job_description' => ['nullable', 'string'],

            'bc_estimate' => ['nullable', 'integer', 'min:1'],

            'extras' => ['nullable', 'string'],

            'special_notes_sequence' => ['nullable', 'string'],

            'source' => ['nullable', 'string'],

        ]);



        if ($validated['job_status'] === WorkOrderJobStatus::CONTINUES) {

            $payload = $this->continueWorkOrder($workOrder, $validated);



            return response()->json(['data' => $payload]);

        }



        $updates = [

            'job_status_id' => WorkOrderJobStatus::idFor($validated['job_status']),

        ];

        if (
            $validated['job_status'] === WorkOrderJobStatus::RESCHEDULED
            && ! empty($validated['execution_date'])
        ) {
            $updates['execution_date'] = $validated['execution_date'];
        }

        $workOrder->update($updates);



        return response()->json([

            'data' => [

                'order' => new WorkOrderResource($this->loadRelations($workOrder->fresh())),

            ],

        ]);

    }



    public function unitAreaMatches(Request $request): AnonymousResourceCollection

    {

        $data = $request->validate([

            'property_id' => ['required', Rule::exists('properties', 'id')],

            'unit_area' => ['required', 'string', 'max:100'],

        ]);



        $orders = $this->workOrdersForPropertyUnitQuery(

            (int) $data['property_id'],

            (string) $data['unit_area'],

        )

            ->with(['property', 'unitSize'])

            ->orderedForDisplay()

            ->get();



        return WorkOrderResource::collection($orders);

    }



    public function store(Request $request): JsonResponse

    {

        $data = $this->validated($request);

        $confirmDifferentUnit = $request->boolean('confirm_different_unit');

        unset($data['confirm_different_unit']);



        $this->assertUnitAreaAllowedForCreate($data, $confirmDifferentUnit);



        $order = DB::transaction(function () use ($data) {

            $nested = $this->extractNested($data);

            $data = $this->applyInitialSiteStatusFromProperty($data);

            $order = WorkOrder::query()->create($data);

            if ($nested !== null) {

                $this->syncNested($order, $nested);

            }



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



    /**

     * @param  array<string, mixed>  $data

     * @return array<string, mixed>

     */

    private function applyInitialSiteStatusFromProperty(array $data): array

    {

        if (array_key_exists('site_status', $data) && $data['site_status'] !== null && $data['site_status'] !== '') {

            return $data;

        }



        if (empty($data['property_id'])) {

            return $data;

        }



        $property = Property::query()->find($data['property_id']);

        if ($property?->initial_site_status) {

            $data['site_status'] = $property->initial_site_status;

        }



        return $data;

    }



    private function loadRelations(WorkOrder $order): WorkOrder

    {

        return $order->load([

            'property.state',

            'property.managementCompany',

            'property.billingClerk',

            'unitSize',

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

            'order_code' => ['prohibited'],

            'execution_date' => [$id === null ? 'required' : 'sometimes', 'date'],

            'property_id' => [$propertyRule, Rule::exists('properties', 'id')],

            'unit_area' => ['nullable', 'string', 'max:100'],

            'unit_size_id' => ['nullable', Rule::exists('unit_sizes', 'id')],

            'site_status' => ['nullable', 'string', 'max:20', SiteOccupancyStatus::nullableRule()],

            'job_status_id' => ['prohibited'],

            'job_description' => ['nullable', 'string'],

            'bc_estimate' => ['nullable', 'integer', 'min:1'],

            'extras' => ['nullable', 'string'],

            'special_notes_sequence' => ['nullable', 'string'],

            'source' => ['nullable', 'string'],

            'request_source_id' => ['nullable', Rule::exists('request_sources', 'id')],

            'references' => ['nullable', 'array'],

            'references.*.reference_type' => ['required_with:references', 'string', 'max:30'],

            'references.*.reference_number' => ['nullable', 'string', 'max:100'],

            'staff' => ['nullable', 'array'],

            'staff.*.employee_id' => ['required_with:staff', Rule::exists('employees', 'id')],

            'staff.*.role_id' => ['required_with:staff', Rule::exists('roles', 'id')],

            'vendor_ids' => ['nullable', 'array'],

            'vendor_ids.*' => ['integer', Rule::exists('vendors', 'id')],

            'confirm_different_unit' => ['sometimes', 'boolean'],

        ]);

    }



    /**

     * @param  array<string, mixed>  $data

     */

    private function assertUnitAreaAllowedForCreate(array $data, bool $confirmDifferentUnit): void

    {

        $unit = trim((string) ($data['unit_area'] ?? ''));

        if ($unit === '' || empty($data['property_id'])) {

            return;

        }



        $existing = $this->workOrdersForPropertyUnitQuery((int) $data['property_id'], $unit)->exists();

        if (! $existing || $confirmDifferentUnit) {

            return;

        }



        throw ValidationException::withMessages([

            'unit_area' => ['This property and unit already have a work order. Confirm if this is a different job before creating another.'],

        ]);

    }



    private function workOrdersForPropertyUnitQuery(int $propertyId, string $unitArea): Builder

    {

        $unit = trim($unitArea);



        return WorkOrder::query()

            ->where('property_id', $propertyId)

            ->whereNotNull('unit_area')

            ->whereRaw('TRIM(unit_area) = ?', [$unit]);

    }



    /**

     * @param  array<string, mixed>  $validated

     * @return array<string, WorkOrderResource>

     */

    private function continueWorkOrder(WorkOrder $source, array $validated): array

    {

        return DB::transaction(function () use ($source, $validated) {

            $source->update([

                'job_status_id' => WorkOrderJobStatus::idFor(WorkOrderJobStatus::CONTINUES),

            ]);



            $newAttributes = [

                'order_code' => $source->order_code,

                'property_id' => $source->property_id,

                'unit_area' => $validated['unit_area'] ?? $source->unit_area,

                'unit_size_id' => $validated['unit_size_id'] ?? $source->unit_size_id,

                'site_status' => $validated['site_status'] ?? $source->site_status,

                'execution_date' => $validated['execution_date'],

                'job_description' => $validated['job_description'] ?? $source->job_description,

                'bc_estimate' => $validated['bc_estimate'] ?? $source->bc_estimate,

                'extras' => $validated['extras'] ?? $source->extras,

                'special_notes_sequence' => $validated['special_notes_sequence'] ?? $source->special_notes_sequence,

                'source' => $validated['source'] ?? $source->source,

                'request_source_id' => $source->request_source_id,

            ];



            $continued = WorkOrder::query()->create($newAttributes);



            return [

                'previous' => new WorkOrderResource($this->loadRelations($source->fresh())),

                'continued_order' => new WorkOrderResource($this->loadRelations($continued)),

            ];

        });

    }

}


