<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PendingJobResource;
use App\Models\PendingJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PendingJobController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PendingJob::query()->with([
            'workOrder.building',
            'workOrder.crew',
            'workOrder.scheduleDay',
        ]);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where('reason_pending', 'like', "%{$search}%");
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->query('job_id'));
        }

        $perPage = min((int) $request->query('per_page', 25), 200);

        return PendingJobResource::collection(
            $query->orderByDesc('pending_id')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $pending = PendingJob::query()->updateOrCreate(
            ['job_id' => $data['job_id']],
            ['reason_pending' => $data['reason_pending'] ?? null],
        );

        return (new PendingJobResource($pending->load(['workOrder.building', 'workOrder.crew', 'workOrder.scheduleDay'])))
            ->response()->setStatusCode(201);
    }

    public function show(PendingJob $pendingJob): PendingJobResource
    {
        return new PendingJobResource(
            $pendingJob->load(['workOrder.building', 'workOrder.crew', 'workOrder.scheduleDay'])
        );
    }

    public function update(Request $request, PendingJob $pendingJob): PendingJobResource
    {
        $pendingJob->update($this->validated($request, $pendingJob->pending_id));

        return new PendingJobResource(
            $pendingJob->fresh()->load(['workOrder.building', 'workOrder.crew', 'workOrder.scheduleDay'])
        );
    }

    public function destroy(PendingJob $pendingJob): JsonResponse
    {
        $pendingJob->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'job_id' => [
                $id ? 'sometimes' : 'required',
                Rule::exists('work_orders', 'job_id'),
                Rule::unique('pending_jobs', 'job_id')->ignore($id, 'pending_id'),
            ],
            'reason_pending' => ['nullable', 'string'],
        ]);
    }
}
