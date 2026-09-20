<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ZoneResource;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ZoneController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Zone::query()->withCount('buildings');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $b) use ($search): void {
                $b->where('zone_name', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%");
            });
        }

        if ($state = trim((string) $request->query('state', ''))) {
            $query->where('state', $state);
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return ZoneResource::collection(
            $query->orderBy('zone_name')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $zone = Zone::query()->create($this->validated($request));

        return (new ZoneResource($zone))->response()->setStatusCode(201);
    }

    public function show(Zone $zone): ZoneResource
    {
        return new ZoneResource($zone->loadCount('buildings'));
    }

    public function update(Request $request, Zone $zone): ZoneResource
    {
        $zone->update($this->validated($request));

        return new ZoneResource($zone->fresh()->loadCount('buildings'));
    }

    public function destroy(Zone $zone): JsonResponse
    {
        $zone->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'zone_name' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:32'],
        ]);
    }
}
