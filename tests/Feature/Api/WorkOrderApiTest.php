<?php

namespace Tests\Feature\Api;

use App\Models\ManagementCompany;
use App\Models\Property;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkOrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_minimal_work_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $state = State::query()->create(['code' => 'NJ', 'name' => 'New Jersey']);
        $management = ManagementCompany::query()->create(['name' => 'Demo Mgmt']);
        $property = Property::query()->create([
            'building_name' => 'Demo Property',
            'address' => '1 Demo St',
            'state_id' => $state->id,
            'management_id' => $management->id,
        ]);

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.property_id', $property->id);
    }
}
