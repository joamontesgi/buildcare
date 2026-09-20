<?php

namespace Tests\Feature\Api;

use App\Models\Building;
use App\Models\CatalogItem;
use App\Models\Management;
use App\Models\ScheduleEntry;
use App\Models\State;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleEntryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_schedule_entries(): void
    {
        $user = User::factory()->create();
        $management = Management::query()->create(['name' => 'AvalonBay', 'is_active' => true]);
        $building = Building::query()->create([
            'address' => 'Test Tower',
            'state_id' => State::query()->where('code', 'NJ')->value('id'),
            'management_id' => $management->id,
        ]);
        ScheduleEntry::query()->create(['building_id' => $building->id]);

        $response = $this->actingAs($user)->getJson('/api/schedule-entries');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.state', 'NJ')
            ->assertJsonPath('data.0.management', 'AvalonBay');
    }

    public function test_entry_reflects_building_changes_without_storing_a_copy(): void
    {
        $user = User::factory()->create();
        $clerk = CatalogItem::query()->create([
            'type' => CatalogItem::TYPE_BC_CLERK,
            'name' => 'A-V',
        ]);
        $zone = Zone::query()->create(['name' => 'Waterfront', 'is_active' => true]);
        $building = Building::query()->create([
            'address' => 'Derived Tower',
            'management_id' => Management::query()->create(['name' => 'Bozzuto'])->id,
        ]);
        $entry = ScheduleEntry::query()->create(['building_id' => $building->id]);

        $building->update([
            'zone_id' => $zone->id,
            'bc_clerk_catalog_item_id' => $clerk->id,
        ]);

        $this->actingAs($user)
            ->getJson("/api/schedule-entries/{$entry->id}")
            ->assertOk()
            ->assertJsonPath('data.management', 'Bozzuto')
            ->assertJsonPath('data.area_section', 'Waterfront')
            ->assertJsonPath('data.bc_clerk', 'A-V');
    }

    public function test_schedule_entry_requires_a_building(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/schedule-entries', ['unit_area' => '101'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('building_id');
    }

    public function test_authenticated_user_can_load_form_catalogs(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/schedule-entries/form-catalogs');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'states',
                    'worksite_statuses',
                    'unit_sizes',
                    'request_types',
                ],
            ]);
    }

    public function test_authenticated_user_can_update_schedule_entry(): void
    {
        $user = User::factory()->create();
        $management = Management::query()->create(['name' => 'AvalonBay', 'is_active' => true]);
        $building = Building::query()->create([
            'address' => 'Tower A',
            'state_id' => State::query()->where('code', 'NJ')->value('id'),
            'management_id' => $management->id,
        ]);
        $entry = ScheduleEntry::query()->create([
            'building_id' => $building->id,
            'unit_area' => '101',
        ]);

        $response = $this->actingAs($user)->putJson("/api/schedule-entries/{$entry->id}", [
            'building_id' => $building->id,
            'unit_area' => '202',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.unit_area', '202');
    }

    public function test_authenticated_user_soft_deletes_schedule_entry(): void
    {
        $user = User::factory()->create();
        $management = Management::query()->create(['name' => 'Mgmt', 'is_active' => true]);
        $building = Building::query()->create([
            'address' => 'Soft Delete Tower',
            'management_id' => $management->id,
        ]);
        $entry = ScheduleEntry::query()->create(['building_id' => $building->id]);

        $this->actingAs($user)
            ->deleteJson("/api/schedule-entries/{$entry->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('schedule_entries', ['id' => $entry->id]);

        $this->actingAs($user)
            ->getJson('/api/schedule-entries')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
