<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleDayResource;
use App\Models\ScheduleDay;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ScheduleDayController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ScheduleDay::query()->with('supervisorOnCall')->withCount('workOrders');

        if ($from = $request->query('from')) {
            $query->whereDate('day_id', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('day_id', '<=', $to);
        }

        if ($request->filled('supervisor_id')) {
            $query->where('supervisor_on_call_id', $request->query('supervisor_id'));
        }

        $perPage = min((int) $request->query('per_page', 30), 200);

        return ScheduleDayResource::collection(
            $query->orderByDesc('day_id')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['day_of_week'] = $data['day_of_week'] ?? Carbon::parse($data['day_id'])->format('l');

        $day = ScheduleDay::query()->create($data);

        return (new ScheduleDayResource(
            $day->load('supervisorOnCall')->loadCount('workOrders')
        ))->response()->setStatusCode(201);
    }

    public function show(string $scheduleDay): ScheduleDayResource
    {
        $day = ScheduleDay::query()
            ->with(['supervisorOnCall', 'workOrders.building', 'workOrders.crew', 'workOrders.pendingJob'])
            ->findOrFail($scheduleDay);

        return new ScheduleDayResource($day);
    }

    public function update(Request $request, string $scheduleDay): ScheduleDayResource
    {
        $day = ScheduleDay::query()->findOrFail($scheduleDay);
        $day->update($this->validated($request, $scheduleDay));

        return new ScheduleDayResource(
            $day->fresh()->load('supervisorOnCall')->loadCount('workOrders')
        );
    }

    public function destroy(string $scheduleDay): JsonResponse
    {
        $day = ScheduleDay::query()->findOrFail($scheduleDay);
        $day->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?string $id = null): array
    {
        $rules = [
            'day_id' => ['required', 'date', Rule::unique('schedule_days', 'day_id')->ignore($id, 'day_id')],
            'day_of_week' => ['nullable', 'string', 'max:20'],
            'supervisor_on_call_id' => ['nullable', Rule::exists('staff', 'staff_id')],
            'default_crews_note' => ['nullable', 'string'],
            'crews_confirmed_note' => ['nullable', 'string'],
            'bc_off_note' => ['nullable', 'string'],
            'crew_off_note' => ['nullable', 'string'],
        ];

        if ($id !== null) {
            $rules['day_id'][0] = 'sometimes';
        }

        return $request->validate($rules);
    }
}
