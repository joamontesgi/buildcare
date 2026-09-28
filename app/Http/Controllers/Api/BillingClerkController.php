<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillingClerkResource;
use App\Models\BillingClerk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class BillingClerkController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = BillingClerk::query()->withCount('properties');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($b) use ($search): void {
                $b->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return BillingClerkResource::collection(
            $query->orderBy('code')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $clerk = BillingClerk::query()->create($this->validated($request));

        return (new BillingClerkResource($clerk))->response()->setStatusCode(201);
    }

    public function show(BillingClerk $billingClerk): BillingClerkResource
    {
        return new BillingClerkResource($billingClerk->loadCount('properties'));
    }

    public function update(Request $request, BillingClerk $billingClerk): BillingClerkResource
    {
        $billingClerk->update($this->validated($request, $billingClerk->id));

        return new BillingClerkResource($billingClerk->fresh()->loadCount('properties'));
    }

    public function destroy(BillingClerk $billingClerk): JsonResponse
    {
        $billingClerk->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('billing_clerks', 'code')->ignore($id)],
            'name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
    }
}
