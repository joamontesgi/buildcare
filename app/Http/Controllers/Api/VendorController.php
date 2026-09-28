<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Vendor::query();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($b) use ($search): void {
                $b->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return VendorResource::collection(
            $query->orderBy('name')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $vendor = Vendor::query()->create($this->validated($request));

        return (new VendorResource($vendor))->response()->setStatusCode(201);
    }

    public function show(Vendor $vendor): VendorResource
    {
        return new VendorResource($vendor);
    }

    public function update(Request $request, Vendor $vendor): VendorResource
    {
        $vendor->update($this->validated($request, $vendor->id));

        return new VendorResource($vendor->fresh());
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $vendor->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('vendors', 'email')->ignore($id)],
        ]);
    }
}
