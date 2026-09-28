<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ManagementCompanyResource;
use App\Models\ManagementCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ManagementCompanyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ManagementCompany::query()->withCount('properties');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = min((int) $request->query('per_page', 25), 100);

        return ManagementCompanyResource::collection(
            $query->orderBy('name')->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $company = ManagementCompany::query()->create($this->validated($request));

        return (new ManagementCompanyResource($company))
            ->response()->setStatusCode(201);
    }

    public function show(ManagementCompany $managementCompany): ManagementCompanyResource
    {
        return new ManagementCompanyResource($managementCompany->loadCount('properties'));
    }

    public function update(Request $request, ManagementCompany $managementCompany): ManagementCompanyResource
    {
        $managementCompany->update($this->validated($request, $managementCompany->id));

        return new ManagementCompanyResource($managementCompany->fresh()->loadCount('properties'));
    }

    public function destroy(ManagementCompany $managementCompany): JsonResponse
    {
        $managementCompany->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('management_companies', 'name')->ignore($id)],
        ]);
    }
}
