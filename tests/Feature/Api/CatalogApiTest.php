<?php

namespace Tests\Feature\Api;

use App\Models\Building;
use App\Models\CatalogItem;
use App\Models\Management;
use App\Models\State;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_buildings(): void
    {
        $user = User::factory()->create();
        $management = Management::query()->create(['name' => 'Greystar', 'is_active' => true]);
        Building::query()->create([
            'address' => 'Test Building',
            'state_id' => State::query()->where('code', 'NJ')->value('id'),
            'management_id' => $management->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/buildings');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.address', 'Test Building')
            ->assertJsonPath('data.0.state_code', 'NJ')
            ->assertJsonPath('data.0.management', 'Greystar');
    }

    public function test_building_state_must_reference_an_existing_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/buildings', [
                'address' => 'Invalid State Tower',
                'state_id' => 999999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('state_id');
    }

    public function test_building_address_must_be_unique(): void
    {
        $user = User::factory()->create();
        Building::query()->create(['address' => 'Duplicated Tower']);

        $this->actingAs($user)
            ->postJson('/api/buildings', ['address' => 'Duplicated Tower'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('address');
    }

    public function test_authenticated_user_can_list_states(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/states?search=New Jersey')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'NJ')
            ->assertJsonPath('data.0.name', 'New Jersey');
    }

    public function test_authenticated_user_can_create_management(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/managements', [
            'name' => 'Bozzuto',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Bozzuto');

        $this->assertDatabaseHas('managements', ['name' => 'Bozzuto']);
    }

    public function test_authenticated_user_can_create_vendor(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/vendors', [
            'name' => 'DVM Angel Davila',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('vendors', ['name' => 'DVM Angel Davila']);
    }

    public function test_authenticated_user_can_list_catalog_items_by_type(): void
    {
        $user = User::factory()->create();

        CatalogItem::query()->create([
            'type' => CatalogItem::TYPE_WORKSITE_STATUS,
            'name' => 'Vacant',
        ]);

        $response = $this->actingAs($user)->getJson('/api/catalog-items?type=worksite_status');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Vacant');
    }

    public function test_guest_cannot_access_catalog_routes(): void
    {
        $this->getJson('/api/buildings')->assertUnauthorized();
        $this->getJson('/api/managements')->assertUnauthorized();
        $this->getJson('/api/vendors')->assertUnauthorized();
        $this->getJson('/api/zones')->assertUnauthorized();
    }
}
