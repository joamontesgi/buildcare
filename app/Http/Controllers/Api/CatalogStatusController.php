<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobStatusResource;
use App\Http\Resources\RequestSourceResource;
use App\Http\Resources\WorksiteStatusResource;
use App\Models\JobStatus;
use App\Models\RequestSource;
use App\Models\WorksiteStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CatalogStatusController extends Controller
{
    public function worksiteStatuses(Request $request): AnonymousResourceCollection
    {
        return $this->listCatalog($request, WorksiteStatus::query(), WorksiteStatusResource::class);
    }

    public function storeWorksiteStatus(Request $request): JsonResponse
    {
        $row = WorksiteStatus::query()->create($this->nameRules($request, 'worksite_statuses'));

        return (new WorksiteStatusResource($row))->response()->setStatusCode(201);
    }

    public function jobStatuses(Request $request): AnonymousResourceCollection
    {
        return $this->listCatalog($request, JobStatus::query(), JobStatusResource::class);
    }

    public function storeJobStatus(Request $request): JsonResponse
    {
        $row = JobStatus::query()->create($this->nameRules($request, 'job_statuses'));

        return (new JobStatusResource($row))->response()->setStatusCode(201);
    }

    public function requestSources(Request $request): AnonymousResourceCollection
    {
        return $this->listCatalog($request, RequestSource::query(), RequestSourceResource::class);
    }

    public function storeRequestSource(Request $request): JsonResponse
    {
        $row = RequestSource::query()->create($this->nameRules($request, 'request_sources'));

        return (new RequestSourceResource($row))->response()->setStatusCode(201);
    }

    /**
     * @param  class-string  $resourceClass
     */
    private function listCatalog(Request $request, $query, string $resourceClass): AnonymousResourceCollection
    {
        if ($search = trim((string) $request->query('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = min((int) $request->query('per_page', 100), 200);

        return $resourceClass::collection(
            $query->orderBy('name')->paginate($perPage)
        );
    }

    /**
     * @return array<string, string>
     */
    private function nameRules(Request $request, string $table): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique($table, 'name')],
        ]);
    }
}
