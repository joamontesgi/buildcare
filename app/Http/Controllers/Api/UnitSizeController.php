<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UnitSizeResource;
use App\Models\UnitSize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class UnitSizeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = UnitSize::query();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = min((int) $request->query('per_page', 50), 100);

        return UnitSizeResource::collection(
            $query->orderBy('name')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $row = UnitSize::query()->create($this->validated($request));

        return (new UnitSizeResource($row))->response()->setStatusCode(201);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('unit_sizes', 'name')->ignore($id)],
        ]);
    }
}
