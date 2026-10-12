<?php

namespace Tests\Feature\Api;

use App\Models\ManagementCompany;
use App\Models\Property;
use App\Models\State;
use App\Models\User;
use App\Support\SiteOccupancyStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkOrderApiTest extends TestCase
{
    use RefreshDatabase;

    private function executionDatePayload(): array
    {
        return ['execution_date' => now()->toDateString()];
    }

    public function test_authenticated_user_can_create_minimal_work_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $state = State::query()->create(['code' => 'NJ', 'name' => 'New Jersey']);
        $management = ManagementCompany::query()->create(['name' => 'Demo Mgmt']);
        $property = Property::query()->create([
            'building_name' => 'Demo Property',
            'address' => '1 Demo St',
            'city' => 'Demo City',
            'zip_code' => '10001',
            'state_id' => $state->id,
            'management_id' => $management->id,
            'initial_site_status' => SiteOccupancyStatus::VACANT,
        ]);

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            ...$this->executionDatePayload(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.property_id', $property->id)
            ->assertJsonPath('data.order_code', 2015)
            ->assertJsonPath('data.site_status', SiteOccupancyStatus::VACANT)
            ->assertJsonPath('data.execution_date', now()->toDateString())
            ->assertJsonPath('data.job_status.name', 'Scheduled');

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            ...$this->executionDatePayload(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.order_code', 2016);

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'order_code' => 9999,
            ...$this->executionDatePayload(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('order_code');

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'bc_estimate' => 4450,
            ...$this->executionDatePayload(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.bc_estimate', 4450);

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'bc_estimate' => 1250.5,
            ...$this->executionDatePayload(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('bc_estimate');

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'bc_estimate' => 0,
            ...$this->executionDatePayload(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('bc_estimate');

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('execution_date');
    }

    public function test_create_requires_confirmation_when_unit_already_has_work_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $state = State::query()->create(['code' => 'NJ', 'name' => 'New Jersey']);
        $management = ManagementCompany::query()->create(['name' => 'Demo Mgmt']);
        $property = Property::query()->create([
            'building_name' => 'Artisan at Lawrenceville',
            'address' => '1 Demo St',
            'city' => 'Demo City',
            'zip_code' => '10001',
            'state_id' => $state->id,
            'management_id' => $management->id,
        ]);

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'unit_area' => '204',
            'job_description' => 'First job',
            ...$this->executionDatePayload(),
        ])->assertCreated();

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'unit_area' => '204',
            'job_description' => 'Second job',
            ...$this->executionDatePayload(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('unit_area');

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'unit_area' => '204',
            'job_description' => 'Second job',
            'confirm_different_unit' => true,
            ...$this->executionDatePayload(),
        ])->assertCreated();

        $this->getJson('/api/work-orders/unit-area-matches?property_id='.$property->id.'&unit_area=204')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_job_status_continue_creates_new_scheduled_work_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $state = State::query()->create(['code' => 'NJ', 'name' => 'New Jersey']);
        $management = ManagementCompany::query()->create(['name' => 'Demo Mgmt']);
        $property = Property::query()->create([
            'building_name' => 'Artisan at Lawrenceville',
            'address' => '1 Demo St',
            'city' => 'Demo City',
            'zip_code' => '10001',
            'state_id' => $state->id,
            'management_id' => $management->id,
        ]);

        $create = $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'unit_area' => '204',
            'job_description' => 'Phase one',
            ...$this->executionDatePayload(),
        ])->assertCreated();

        $orderId = $create->json('data.id');
        $orderCode = $create->json('data.order_code');

        $this->postJson("/api/work-orders/{$orderId}/job-status", [
            'job_status' => 'Continues',
            'execution_date' => now()->addDays(3)->toDateString(),
            'job_description' => 'Phase two',
        ])
            ->assertOk()
            ->assertJsonPath('data.previous.job_status.name', 'Continues')
            ->assertJsonPath('data.continued_order.job_status.name', 'Scheduled')
            ->assertJsonPath('data.continued_order.job_description', 'Phase two')
            ->assertJsonPath('data.continued_order.unit_area', '204')
            ->assertJsonPath('data.continued_order.order_code', $orderCode)
            ->assertJsonPath('data.previous.order_code', $orderCode);
    }

    public function test_index_filters_by_execution_date_range(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $state = State::query()->create(['code' => 'NJ', 'name' => 'New Jersey']);
        $management = ManagementCompany::query()->create(['name' => 'Demo Mgmt']);
        $property = Property::query()->create([
            'building_name' => 'Demo Property',
            'address' => '1 Demo St',
            'city' => 'Demo City',
            'zip_code' => '10001',
            'state_id' => $state->id,
            'management_id' => $management->id,
        ]);

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'execution_date' => '2026-10-05',
        ])->assertCreated();

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'execution_date' => '2026-10-15',
        ])->assertCreated();

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'execution_date' => '2026-10-25',
        ])->assertCreated();

        $this->getJson('/api/work-orders?execution_date_from=2026-10-10&execution_date_to=2026-10-20&per_page=50')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.execution_date', '2026-10-15');
    }

    public function test_index_lists_work_orders_by_execution_date_then_order_code(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $state = State::query()->create(['code' => 'NJ', 'name' => 'New Jersey']);
        $management = ManagementCompany::query()->create(['name' => 'Demo Mgmt']);
        $property = Property::query()->create([
            'building_name' => 'Demo Property',
            'address' => '1 Demo St',
            'city' => 'Demo City',
            'zip_code' => '10001',
            'state_id' => $state->id,
            'management_id' => $management->id,
        ]);

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'execution_date' => '2026-10-20',
        ])->assertCreated();

        $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            'execution_date' => '2026-10-10',
        ])->assertCreated();

        $this->getJson('/api/work-orders?per_page=50')
            ->assertOk()
            ->assertJsonPath('data.0.execution_date', '2026-10-10')
            ->assertJsonPath('data.1.execution_date', '2026-10-20');
    }

    public function test_job_status_can_be_changed_after_done(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $state = State::query()->create(['code' => 'NJ', 'name' => 'New Jersey']);
        $management = ManagementCompany::query()->create(['name' => 'Demo Mgmt']);
        $property = Property::query()->create([
            'building_name' => 'Demo Property',
            'address' => '1 Demo St',
            'city' => 'Demo City',
            'zip_code' => '10001',
            'state_id' => $state->id,
            'management_id' => $management->id,
        ]);

        $create = $this->postJson('/api/work-orders', [
            'property_id' => $property->id,
            ...$this->executionDatePayload(),
        ])->assertCreated();

        $orderId = $create->json('data.id');

        $this->postJson("/api/work-orders/{$orderId}/job-status", [
            'job_status' => 'Done',
        ])->assertOk()->assertJsonPath('data.order.job_status.name', 'Done');

        $this->postJson("/api/work-orders/{$orderId}/job-status", [
            'job_status' => 'Scheduled',
        ])->assertOk()->assertJsonPath('data.order.job_status.name', 'Scheduled');
    }
}
