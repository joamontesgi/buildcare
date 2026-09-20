<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\ManagementCompany;
use App\Models\PendingJob;
use App\Models\ScheduleDay;
use App\Models\Staff;
use App\Models\Subcontractor;
use App\Models\SubcontractorEmployee;
use App\Models\WorkOrder;
use App\Models\Zone;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class ErdDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Management Companies
        $managements = collect([
            ['name' => 'Silver Property Group'],
            ['name' => 'Bluewater Residential'],
            ['name' => 'Oakwood Communities'],
        ])->map(fn (array $row) => ManagementCompany::query()->firstOrCreate($row));

        // 2) Zones
        $zones = collect([
            ['zone_name' => 'North Miami', 'state' => 'FL'],
            ['zone_name' => 'Aventura', 'state' => 'FL'],
            ['zone_name' => 'Coral Gables', 'state' => 'FL'],
            ['zone_name' => 'Downtown Atlanta', 'state' => 'GA'],
        ])->map(fn (array $row) => Zone::query()->firstOrCreate($row));

        // 3) Staff
        $supervisors = collect([
            ['name' => 'Jorge Rivera', 'role' => Staff::ROLE_SUPERVISOR, 'phone' => '305-555-0111', 'email' => 'jrivera@buildcare.com'],
            ['name' => 'Marta Peña', 'role' => Staff::ROLE_SUPERVISOR, 'phone' => '305-555-0122', 'email' => 'mpena@buildcare.com'],
            ['name' => 'Luis Ortega', 'role' => Staff::ROLE_RUNNER, 'phone' => '305-555-0133', 'email' => 'lortega@buildcare.com'],
            ['name' => 'Ana García', 'role' => Staff::ROLE_MANAGER, 'phone' => '305-555-0144', 'email' => 'agarcia@buildcare.com'],
        ])->map(fn (array $row) => Staff::query()->firstOrCreate(['email' => $row['email']], $row));

        // 4) Buildings
        $buildings = collect([
            [
                'name' => 'Silver Palm Tower',
                'address' => '1234 NE 4th Ave, Miami, FL 33132',
                'state' => 'FL',
                'management_id' => $managements[0]->management_id,
                'zone_id' => $zones[0]->zone_id,
                'bc_supervisor_id' => $supervisors[0]->staff_id,
                'billing_clerk_code' => 'BC-001',
                'travel_expense' => '$45 flat',
                'paint_specs' => 'SW Alabaster / SW Iron Ore trim',
                'notes' => 'Access via loading dock',
            ],
            [
                'name' => 'Bluewater Marina Lofts',
                'address' => '900 NW 79th St, Miami, FL 33150',
                'state' => 'FL',
                'management_id' => $managements[1]->management_id,
                'zone_id' => $zones[0]->zone_id,
                'bc_supervisor_id' => $supervisors[1]->staff_id,
                'billing_clerk_code' => 'BC-002',
                'travel_expense' => '$60 flat',
                'paint_specs' => 'Behr Ultra Pure White',
                'notes' => 'Reserve elevator for oversized loads',
            ],
            [
                'name' => 'Oakwood Coral Residences',
                'address' => '450 Alhambra Cir, Coral Gables, FL 33134',
                'state' => 'FL',
                'management_id' => $managements[2]->management_id,
                'zone_id' => $zones[2]->zone_id,
                'bc_supervisor_id' => $supervisors[0]->staff_id,
                'billing_clerk_code' => 'BC-003',
                'travel_expense' => '$75 flat',
                'paint_specs' => 'BM White Dove & Kendall Charcoal',
                'notes' => 'HOA approval required for exterior work',
            ],
            [
                'name' => 'Downtown Peachtree Suites',
                'address' => '55 Peachtree St NE, Atlanta, GA 30303',
                'state' => 'GA',
                'management_id' => $managements[0]->management_id,
                'zone_id' => $zones[3]->zone_id,
                'bc_supervisor_id' => $supervisors[1]->staff_id,
                'billing_clerk_code' => 'BC-004',
                'travel_expense' => '$120 travel',
                'paint_specs' => null,
                'notes' => 'Weekend work only',
            ],
        ])->map(fn (array $row) => Building::query()->firstOrCreate(
            ['name' => $row['name']],
            $row,
        ));

        // 5) Subcontractors (con empleados)
        $subcontractorSeed = [
            [
                'company_name' => 'Reliable Painting LLC',
                'contact_name' => 'Carlos Núñez',
                'phone' => '305-555-1001',
                'capabilities' => 'Painting, drywall patching, trim & baseboards',
                'employees' => [
                    ['name' => 'Carlos Núñez', 'phone' => '305-555-1002', 'role' => 'Owner / Lead Painter'],
                    ['name' => 'Diego Lozano', 'phone' => '305-555-1003', 'role' => 'Painter'],
                ],
            ],
            [
                'company_name' => 'ProShine Cleaners',
                'contact_name' => 'Yulia Márquez',
                'phone' => '305-555-2001',
                'capabilities' => 'Deep cleaning, carpet extraction, marble polishing',
                'employees' => [
                    ['name' => 'Yulia Márquez', 'phone' => '305-555-2002', 'role' => 'Supervisor'],
                    ['name' => 'Rosa Betancourt', 'phone' => '305-555-2003', 'role' => 'Cleaner'],
                    ['name' => 'Ivette Salgado', 'phone' => '305-555-2004', 'role' => 'Cleaner'],
                ],
            ],
            [
                'company_name' => 'HandyCore Services',
                'contact_name' => 'Kenneth Reed',
                'phone' => '305-555-3001',
                'capabilities' => 'Plumbing, drywall, general handyman, minor electrical',
                'employees' => [
                    ['name' => 'Kenneth Reed', 'phone' => '305-555-3002', 'role' => 'Owner'],
                    ['name' => 'Bill Harper', 'phone' => '305-555-3003', 'role' => 'Handyman'],
                ],
            ],
        ];

        $subcontractors = collect();
        foreach ($subcontractorSeed as $row) {
            $employees = $row['employees'];
            unset($row['employees']);

            $sub = Subcontractor::query()->firstOrCreate(
                ['company_name' => $row['company_name']],
                $row,
            );

            foreach ($employees as $emp) {
                SubcontractorEmployee::query()->firstOrCreate(
                    ['subcontractor_id' => $sub->subcontractor_id, 'name' => $emp['name']],
                    $emp,
                );
            }

            $subcontractors->push($sub);
        }

        // 6) Schedule Days (5 días recientes) con cabecera estilo Excel completa
        $days = collect(range(0, 4))->map(function (int $offset) use ($supervisors) {
            $date = CarbonImmutable::today()->subDays($offset);

            return ScheduleDay::query()->updateOrCreate(
                ['day_id' => $date->toDateString()],
                [
                    'day_of_week' => $date->format('l'),
                    'supervisor_on_call_id' => $supervisors[$offset % 2]->staff_id,
                    'default_crews_note' => 'Reliable Painting / ProShine Cleaners',
                    'crews_confirmed_note' => $offset === 0 ? 'Reliable Painting confirms 3 techs' : 'Pending confirmation',
                    'bc_off_note' => $offset === 0 ? 'Ana García (vacation)' : '—',
                    'crew_off_note' => $offset === 0 ? 'HandyCore – Bill Harper (personal)' : '—',
                ],
            );
        });

        // 7) Work Orders demo
        $sampleOrders = [
            [
                'unit_area' => 'Unit 802',
                'size' => '2/2',
                'worksite_status' => WorkOrder::STATUS_IN_PROGRESS,
                'job_description' => 'Full unit repaint after tenant move-out',
                'request_po_wtn_wo' => 'PO#88231',
                'bc_work_order' => 'BCWO-500',
                'bc_estimate_ref' => 'EST-1042',
                'extras' => 'Patch drywall in living room',
                'special_notes' => 'Owner requests satin finish on trim',
                'vendor_status_report' => 'Painters day 2 of 3',
            ],
            [
                'unit_area' => 'Common Area – Lobby',
                'size' => 'N/A',
                'worksite_status' => WorkOrder::STATUS_SCHEDULED,
                'job_description' => 'Deep clean carpets & polish marble floor',
                'request_po_wtn_wo' => 'WTN#5521',
                'bc_work_order' => 'BCWO-501',
                'bc_estimate_ref' => 'EST-1050',
                'extras' => null,
                'special_notes' => 'Coordinate with concierge for access',
                'vendor_status_report' => null,
            ],
            [
                'unit_area' => 'Unit 305',
                'size' => '1/1',
                'worksite_status' => WorkOrder::STATUS_PENDING,
                'job_description' => 'Water damage remediation in bathroom',
                'request_po_wtn_wo' => 'WO#7101',
                'bc_work_order' => 'BCWO-502',
                'bc_estimate_ref' => 'EST-1061',
                'extras' => 'Awaiting owner approval on tile selection',
                'special_notes' => 'Insurance claim in review',
                'vendor_status_report' => 'On hold pending approval',
            ],
            [
                'unit_area' => 'Roof – North Wing',
                'size' => 'N/A',
                'worksite_status' => WorkOrder::STATUS_COMPLETED,
                'job_description' => 'Silicone recoat inspection',
                'request_po_wtn_wo' => 'PO#88540',
                'bc_work_order' => 'BCWO-503',
                'bc_estimate_ref' => 'EST-1099',
                'extras' => null,
                'special_notes' => 'Warranty renewed for 5 years',
                'vendor_status_report' => 'Completed, report attached',
            ],
        ];

        foreach ($days as $index => $day) {
            // El día de HOY (offset 0) recibe una agenda rica cubriendo los 4 buildings
            // (3 zonas de FL + 1 de GA) para poder ver la agrupación estado→zona.
            if ($index === 0) {
                foreach ($buildings as $bIdx => $building) {
                    $sub = $subcontractors[$bIdx % $subcontractors->count()];
                    $orderTemplate = $sampleOrders[$bIdx % count($sampleOrders)];

                    $order = WorkOrder::query()->create([
                        'day_id' => $day->day_id,
                        'building_id' => $building->building_id,
                        'subcontractor_id' => $sub->subcontractor_id,
                        ...$orderTemplate,
                    ]);

                    if ($order->worksite_status === WorkOrder::STATUS_PENDING) {
                        PendingJob::query()->updateOrCreate(
                            ['job_id' => $order->job_id],
                            ['reason_pending' => 'Awaiting client approval on materials'],
                        );
                    }
                }

                continue;
            }

            // Días anteriores: 1 work order por día como referencia.
            $building = $buildings[$index % $buildings->count()];
            $sub = $subcontractors[$index % $subcontractors->count()];
            $orderTemplate = $sampleOrders[$index % count($sampleOrders)];

            $order = WorkOrder::query()->create([
                'day_id' => $day->day_id,
                'building_id' => $building->building_id,
                'subcontractor_id' => $sub->subcontractor_id,
                ...$orderTemplate,
            ]);

            if ($order->worksite_status === WorkOrder::STATUS_PENDING) {
                PendingJob::query()->updateOrCreate(
                    ['job_id' => $order->job_id],
                    ['reason_pending' => 'Awaiting client approval on materials'],
                );
            }
        }
    }
}
