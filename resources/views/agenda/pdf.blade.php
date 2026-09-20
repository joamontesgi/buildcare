<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $agenda['title'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; margin: 0; padding: 12px 14px; }
        h1.title {
            background: #1f2937; color: #fff; padding: 8px 12px;
            font-size: 14px; text-align: center; margin: 0 0 8px; letter-spacing: 0.5px;
        }
        .header-line { padding: 3px 6px; border-bottom: 1px solid #e5e7eb; font-weight: bold; }
        .state {
            background: #dbeafe; color: #1e3a8a; padding: 6px 8px; text-align: center;
            font-weight: bold; font-size: 12px; margin: 10px 0 4px;
        }
        .zone {
            background: #fef3c7; padding: 4px 8px; font-weight: bold; font-size: 11px;
        }
        .supervisor { font-style: italic; padding: 2px 8px 6px; font-size: 10px; }
        table.orders {
            width: 100%; border-collapse: collapse; margin-bottom: 8px;
            table-layout: fixed;
        }
        table.orders th {
            background: #111827; color: #fff; padding: 4px 3px; font-size: 8px;
            border: 1px solid #6b7280; text-align: center; vertical-align: middle;
        }
        table.orders td {
            border: 1px solid #d1d5db; padding: 3px 4px; vertical-align: top;
            font-size: 8px; word-wrap: break-word; white-space: pre-line;
        }
        tr.pending td { background: #fee2e2; }
        .totals { margin-top: 12px; font-size: 9px; color: #4b5563; text-align: right; }
        .col-state    { width: 3%; }
        .col-mgmt     { width: 9%; }
        .col-clerk    { width: 5%; }
        .col-building { width: 13%; }
        .col-unit     { width: 6%; }
        .col-size     { width: 5%; }
        .col-status   { width: 7%; }
        .col-desc     { width: 15%; }
        .col-awarded  { width: 10%; }
        .col-request  { width: 7%; }
        .col-bcwo     { width: 8%; }
        .col-extras   { width: 5%; }
        .col-special  { width: 8%; }
        .col-vendor   { width: 8%; }
    </style>
</head>
<body>
    <h1 class="title">{{ $agenda['title'] }}</h1>

    @php $h = $agenda['header']; @endphp
    <div class="header-line">
        SUPERVISOR ON CALL:
        @if ($h['supervisor_on_call'])
            {{ $h['supervisor_on_call']['name'] }}
            @if (!empty($h['supervisor_on_call']['phone'])) &nbsp;{{ $h['supervisor_on_call']['phone'] }} @endif
            @if (!empty($h['supervisor_on_call']['email'])) &nbsp;{{ $h['supervisor_on_call']['email'] }} @endif
        @else — @endif
    </div>
    <div class="header-line">CREWS ASSIGNED BY DEFAULT ON CALL: {{ $h['default_crews_note'] ?? '—' }}</div>
    <div class="header-line">CREWS CONFIRMED BY THE ON CALL: {{ $h['crews_confirmed_note'] ?? '—' }}</div>
    <div class="header-line">BC MEMBERS OFF/VACATIONS: {{ $h['bc_off_note'] ?? '—' }}</div>
    <div class="header-line">CREW MEMBERS OFF/VACATIONS: {{ $h['crew_off_note'] ?? '—' }}</div>

    @foreach ($agenda['sections'] as $section)
        <div class="state">STATE OF {{ $section['state_name'] }}</div>

        @foreach ($section['zones'] as $zone)
            <div class="zone">{{ strtoupper($zone['zone_name']) }}</div>
            <div class="supervisor">
                BC SUPERVISOR:
                @if ($zone['bc_supervisor'])
                    {{ $zone['bc_supervisor']['name'] }}
                    @if (!empty($zone['bc_supervisor']['phone'])) &nbsp;{{ $zone['bc_supervisor']['phone'] }} @endif
                    @if (!empty($zone['bc_supervisor']['email'])) &nbsp;{{ $zone['bc_supervisor']['email'] }} @endif
                @else — @endif
            </div>

            <table class="orders">
                <thead>
                    <tr>
                        @php
                            $colClasses = ['col-state','col-mgmt','col-clerk','col-building','col-unit','col-size','col-status','col-desc','col-awarded','col-request','col-bcwo','col-extras','col-special','col-vendor'];
                        @endphp
                        @foreach ($agenda['columns'] as $i => $label)
                            <th class="{{ $colClasses[$i] ?? '' }}">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($zone['work_orders'] as $order)
                        <tr class="{{ $order['is_pending'] ? 'pending' : '' }}">
                            @foreach ($order['cells'] as $cell)
                                <td>{{ $cell ?? '' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="14" style="text-align:center;color:#6b7280;">No work orders scheduled.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    @endforeach

    <div class="totals">
        Total work orders: {{ $agenda['totals']['work_orders'] }} —
        States: {{ $agenda['totals']['states'] }} —
        Zones: {{ $agenda['totals']['zones'] }}
    </div>
</body>
</html>
