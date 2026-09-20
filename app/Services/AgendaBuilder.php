<?php

namespace App\Services;

use App\Models\ScheduleDay;
use Illuminate\Support\Collection;

/**
 * Construye la estructura de la agenda diaria replicando el layout del Excel:
 *
 *   BuildCare SCHEDULE <DÍA, FECHA>
 *   Supervisor on call / crews assigned / crews confirmed / off notes ...
 *
 *   STATE OF <NOMBRE>
 *     <ZONA> — BC SUPERVISOR: <staff>
 *       [work orders...]
 *     <ZONA> — BC SUPERVISOR: <staff>
 *       [work orders...]
 */
class AgendaBuilder
{
    /**
     * Mapa mínimo de códigos de estado (extensible). Los edificios almacenan
     * códigos cortos (ej. "FL", "NJ"); el Excel muestra el nombre completo.
     */
    public const STATE_NAMES = [
        'FL' => 'FLORIDA',
        'NJ' => 'NEW JERSEY',
        'NY' => 'NEW YORK',
        'GA' => 'GEORGIA',
        'PA' => 'PENNSYLVANIA',
        'CT' => 'CONNECTICUT',
        'MA' => 'MASSACHUSETTS',
        'TX' => 'TEXAS',
        'CA' => 'CALIFORNIA',
    ];

    /**
     * Etiquetas de las 14 columnas de la tabla, iguales al Excel de referencia.
     *
     * @var array<int, string>
     */
    public const COLUMN_LABELS = [
        'State',
        'MANAGEMENT',
        'BC CLERK',
        'BUILDING / ADDRESS',
        'UNIT / AREA',
        'SIZE',
        'WORKSITE STATUS',
        'JOB DESCRIPTION',
        'JOB AWARDED TO: / TECHS QUANTITY:',
        'REQUEST / P.O / WTN / W.O',
        'BC WORK ORDER / BC ESTIMATE',
        'EXTRAS',
        'SPECIAL NOTES / SEQUENCE',
        'VENDORS DAILY STATUS REPORT',
    ];

    /**
     * @return array<string, mixed>
     */
    public function build(string $dayId): array
    {
        $day = ScheduleDay::query()
            ->with([
                'supervisorOnCall',
                'workOrders.building.managementCompany',
                'workOrders.building.zone',
                'workOrders.building.bcSupervisor',
                'workOrders.subcontractor.employees',
                'workOrders.pendingJob',
            ])
            ->findOrFail($dayId);

        $orders = $day->workOrders ?? collect();

        // Agrupación anidada: state (código) -> zona (id o "Sin zona") -> [work orders]
        $states = $orders->groupBy(fn ($wo) => (string) ($wo->building?->state ?? 'ZZ'));

        $sections = [];
        foreach ($states as $stateCode => $stateOrders) {
            $stateName = self::STATE_NAMES[$stateCode] ?? ($stateCode === 'ZZ' ? 'NO STATE' : strtoupper($stateCode));

            $zones = [];
            $byZone = $stateOrders->groupBy(fn ($wo) => (string) ($wo->building?->zone_id ?? '0'));

            foreach ($byZone as $zoneKey => $zoneOrders) {
                $anyBuilding = $zoneOrders->first()?->building;
                $zone = $anyBuilding?->zone;
                $supervisor = $anyBuilding?->bcSupervisor;

                $zones[] = [
                    'zone_id' => $zone?->zone_id,
                    'zone_name' => $zone?->zone_name ?? 'UNZONED',
                    'bc_supervisor' => $supervisor ? [
                        'staff_id' => $supervisor->staff_id,
                        'name' => $supervisor->name,
                        'phone' => $supervisor->phone,
                        'email' => $supervisor->email,
                    ] : null,
                    'work_orders' => $this->flattenOrders($zoneOrders),
                ];
            }

            // Ordenar zonas alfabéticamente (SIN ZONA al final)
            usort($zones, function (array $a, array $b): int {
                if ($a['zone_name'] === 'UNZONED') return 1;
                if ($b['zone_name'] === 'UNZONED') return -1;
                return strcmp($a['zone_name'], $b['zone_name']);
            });

            $sections[] = [
                'state_code' => $stateCode === 'ZZ' ? null : $stateCode,
                'state_name' => $stateName,
                'zones' => $zones,
                'total_work_orders' => $stateOrders->count(),
            ];
        }

        // Ordenar estados alfabéticamente (SIN ESTADO al final)
        usort($sections, function (array $a, array $b): int {
            if ($a['state_name'] === 'NO STATE') return 1;
            if ($b['state_name'] === 'NO STATE') return -1;
            return strcmp($a['state_name'], $b['state_name']);
        });

        $dayFormatted = optional($day->day_id)->format('l, F j / Y') ?? $dayId;
        $dayShort = optional($day->day_id)->format('Y-m-d') ?? $dayId;

        return [
            'day_id' => $dayShort,
            'day_of_week' => $day->day_of_week ?? optional($day->day_id)->format('l'),
            'title' => 'BuildCare SCHEDULE '.strtoupper($dayFormatted),
            'header' => [
                'supervisor_on_call' => $day->supervisorOnCall ? [
                    'name' => $day->supervisorOnCall->name,
                    'phone' => $day->supervisorOnCall->phone,
                    'email' => $day->supervisorOnCall->email,
                ] : null,
                'default_crews_note' => $day->default_crews_note,
                'crews_confirmed_note' => $day->crews_confirmed_note,
                'bc_off_note' => $day->bc_off_note,
                'crew_off_note' => $day->crew_off_note,
            ],
            'columns' => self::COLUMN_LABELS,
            'sections' => $sections,
            'totals' => [
                'work_orders' => $orders->count(),
                'states' => count($sections),
                'zones' => array_sum(array_map(fn (array $s) => count($s['zones']), $sections)),
            ],
        ];
    }

    /**
     * Aplana las órdenes al formato de fila que consume la vista/Excel.
     *
     * @param  Collection<int, \App\Models\WorkOrder>  $orders
     * @return array<int, array<string, mixed>>
     */
    private function flattenOrders(Collection $orders): array
    {
        return $orders->map(function ($wo): array {
            $building = $wo->building;
            $subcontractor = $wo->subcontractor;

            $buildingLabel = trim(
                ($building?->name ?? '').(
                    $building?->address ? "\n".$building->address : ''
                )
            );

            $assignedIds = collect($wo->assigned_employee_ids ?? [])->map(fn ($id) => (int) $id)->all();
            $techNames = [];
            if ($subcontractor && $assignedIds !== []) {
                $techNames = $subcontractor->employees
                    ? $subcontractor->employees->whereIn('employee_id', $assignedIds)->pluck('name')->all()
                    : [];
            }

            if ($techNames !== []) {
                $subLabel = trim(($subcontractor?->company_name ?? '').' '.implode(' / ', $techNames));
            } else {
                $subLabel = $subcontractor?->company_name.
                    ($subcontractor?->contact_name ? ' — '.$subcontractor->contact_name : '');
            }

            $bcWoEstimate = trim(
                ($wo->bc_work_order ? 'BCWO: '.$wo->bc_work_order : '').
                ($wo->bc_estimate_ref ? ($wo->bc_work_order ? "\n" : '').'Estimate: '.$wo->bc_estimate_ref : '')
            );

            return [
                'job_id' => $wo->job_id,
                'cells' => [
                    // Orden idéntico al array COLUMN_LABELS.
                    $building?->state,
                    $building?->managementCompany?->name,
                    $building?->billing_clerk_code,
                    $buildingLabel !== '' ? $buildingLabel : null,
                    $wo->unit_area,
                    $wo->size,
                    $wo->worksite_status,
                    $wo->job_description,
                    trim($subLabel) !== '' ? $subLabel : null,
                    $wo->request_po_wtn_wo,
                    $bcWoEstimate !== '' ? $bcWoEstimate : null,
                    $wo->extras,
                    $wo->special_notes,
                    $wo->vendor_status_report,
                ],
                'is_pending' => (bool) $wo->pendingJob,
            ];
        })->values()->all();
    }
}
