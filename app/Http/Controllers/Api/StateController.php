<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StateResource;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class StateController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = State::query()->withCount('properties');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($b) use ($search): void {
                $b->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 50), 100);

        return StateResource::collection(
            $query->orderBy('code')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $state = State::query()->create($this->validated($request));

        return (new StateResource($state))->response()->setStatusCode(201);
    }

    public function show(State $state): StateResource
    {
        return new StateResource($state->loadCount('properties'));
    }

    public function update(Request $request, State $state): StateResource
    {
        $state->update($this->validated($request, $state->id));

        return new StateResource($state->fresh()->loadCount('properties'));
    }

    public function destroy(State $state): JsonResponse
    {
        $state->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('states', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:100'],
        ]);
    }
}
