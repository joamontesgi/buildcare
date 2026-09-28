<?php

namespace Tests\Feature\Api;

use App\Models\ManagementCompany;
use App\Models\Property;
use App\Models\State;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_properties_via_buildings_alias(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $state = State::query()->create(['code' => 'NJ', 'name' => 'New Jersey']);
        $management = ManagementCompany::query()->create(['name' => 'Greystar']);
        Property::query()->create([
            'building_name' => 'Test Tower',
            'address' => '100 Main St',
            'state_id' => $state->id,
            'management_id' => $management->id,
        ]);

        $this->getJson('/api/buildings')
            ->assertOk()
            ->assertJsonPath('data.0.building_name', 'Test Tower')
            ->assertJsonPath('data.0.address', '100 Main St');
    }

    public function test_property_requires_valid_state(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $management = ManagementCompany::query()->create(['name' => 'Greystar']);

        $this->postJson('/api/properties', [
            'building_name' => 'Invalid State Tower',
            'address' => '100 Main St',
            'state_id' => 999999,
            'management_id' => $management->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('state_id');
    }

    public function test_authenticated_user_can_list_states(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        State::query()->create(['code' => 'NJ', 'name' => 'New Jersey']);

        $this->getJson('/api/states?search=New Jersey')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'NJ')
            ->assertJsonPath('data.0.name', 'New Jersey');
    }

    public function test_authenticated_user_can_create_management_company(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/management-companies', ['name' => 'Bozzuto'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Bozzuto');

        $this->assertDatabaseHas('management_companies', ['name' => 'Bozzuto']);
    }

    public function test_authenticated_user_can_create_vendor(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/vendors', ['name' => 'Acme Services'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Acme Services');
    }

    public function test_guest_cannot_access_catalog_routes(): void
    {
        $this->getJson('/api/properties')->assertUnauthorized();
        $this->getJson('/api/management-companies')->assertUnauthorized();
        $this->getJson('/api/vendors')->assertUnauthorized();
        $this->getJson('/api/states')->assertUnauthorized();
    }
}
