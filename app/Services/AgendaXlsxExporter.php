<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Exporta la agenda diaria a XLSX replicando el layout del Excel de referencia
 * ("Schedule ... 2026 -2.xlsx"): cabecera del día, secciones por estado y zona
 * con cabecera de supervisor, y una tabla de 14 columnas con las work orders.
 */
class AgendaXlsxExporter
{
    private const HIGHEST_COLUMN = 'N'; // 14 columnas

    private const COLOR_TITLE_BG = 'FF1F2937';
    private const COLOR_TITLE_TEXT = 'FFFFFFFF';
    private const COLOR_STATE_BG = 'FFD1E7FF';
    private const COLOR_ZONE_BG = 'FFFEF3C7';
    private const COLOR_HEADER_BG = 'FF111827';
    private const COLOR_HEADER_TEXT = 'FFFFFFFF';
    private const COLOR_PENDING_BG = 'FFFEE2E2';

    /**
     * Genera el XLSX y devuelve la ruta absoluta al archivo temporal.
     */
    public function export(array $agenda): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($agenda['day_id'] ?? 'Agenda', 0, 30));

        $row = 1;

        // --- Título grande ---
        $sheet->setCellValue("A{$row}", $agenda['title']);
        $sheet->mergeCells("A{$row}:".self::HIGHEST_COLUMN.$row);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => self::COLOR_TITLE_TEXT]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TITLE_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(28);
        $row += 2;

        // --- Cabecera de datos del día ---
        $header = $agenda['header'] ?? [];

        $supervisorText = 'SUPERVISOR ON CALL: ';
        if ($sup = $header['supervisor_on_call'] ?? null) {
            $supervisorText .= $sup['name'];
            if (!empty($sup['phone'])) $supervisorText .= '  '.$sup['phone'];
            if (!empty($sup['email'])) $supervisorText .= '  '.$sup['email'];
        } else {
            $supervisorText .= '—';
        }

        foreach ([
            $supervisorText,
            'CREWS ASSIGNED BY DEFAULT ON CALL: '.($header['default_crews_note'] ?? '—'),
            'CREWS CONFIRMED BY THE ON CALL: '.($header['crews_confirmed_note'] ?? '—'),
            'BC MEMBERS OFF/VACATIONS: '.($header['bc_off_note'] ?? '—'),
            'CREW MEMBERS OFF/VACATIONS: '.($header['crew_off_note'] ?? '—'),
        ] as $line) {
            $sheet->setCellValue("A{$row}", $line);
            $sheet->mergeCells("A{$row}:".self::HIGHEST_COLUMN.$row);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        $row += 1; // línea en blanco

        // --- Secciones por estado / zona ---
        foreach ($agenda['sections'] as $section) {
            // Encabezado de estado
            $sheet->setCellValue("A{$row}", 'STATE OF '.$section['state_name']);
            $sheet->mergeCells("A{$row}:".self::HIGHEST_COLUMN.$row);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 13],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_STATE_BG]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(22);
            $row += 2;

            foreach ($section['zones'] as $zone) {
                // Encabezado de zona
                $sheet->setCellValue("A{$row}", strtoupper($zone['zone_name']));
                $sheet->mergeCells("A{$row}:".self::HIGHEST_COLUMN.$row);
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_ZONE_BG]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(20);
                $row++;

                if ($sup = $zone['bc_supervisor']) {
                    $line = 'BC SUPERVISOR: '.$sup['name'];
                    if (!empty($sup['phone'])) $line .= '  '.$sup['phone'];
                    if (!empty($sup['email'])) $line .= '  '.$sup['email'];
                } else {
                    $line = 'BC SUPERVISOR: —';
                }

                $sheet->setCellValue("A{$row}", $line);
                $sheet->mergeCells("A{$row}:".self::HIGHEST_COLUMN.$row);
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 11],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $row++;

                // Encabezado de tabla de work orders (14 columnas)
                $col = 'A';
                foreach ($agenda['columns'] as $label) {
                    $sheet->setCellValue($col.$row, $label);
                    $col = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($col) + 1);
                }
                $sheet->getStyle("A{$row}:".self::HIGHEST_COLUMN.$row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => self::COLOR_HEADER_TEXT], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF9CA3AF']]],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(32);
                $row++;

                // Filas de work orders
                foreach ($zone['work_orders'] as $order) {
                    $col = 'A';
                    foreach ($order['cells'] as $value) {
                        $sheet->setCellValue($col.$row, (string) ($value ?? ''));
                        $col = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($col) + 1);
                    }

                    $rowStyle = [
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                        'font' => ['size' => 10],
                    ];

                    if ($order['is_pending'] ?? false) {
                        $rowStyle['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_PENDING_BG]];
                    }

                    $sheet->getStyle("A{$row}:".self::HIGHEST_COLUMN.$row)->applyFromArray($rowStyle);
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                    $row++;
                }

                $row++; // separador entre zonas
            }
        }

        // --- Anchos de columna ---
        $widths = [
            'A' => 6,   // State
            'B' => 20,  // Management
            'C' => 10,  // BC Clerk
            'D' => 32,  // Building / Address
            'E' => 12,  // Unit / Area
            'F' => 8,   // Size
            'G' => 14,  // Worksite Status
            'H' => 45,  // Job Description
            'I' => 26,  // Job Awarded To
            'J' => 18,  // Request / PO
            'K' => 22,  // BC WO / Estimate
            'L' => 16,  // Extras
            'M' => 28,  // Special Notes
            'N' => 26,  // Vendors Status
        ];
        foreach ($widths as $letter => $width) {
            $sheet->getColumnDimension($letter)->setWidth($width);
        }

        // Escribir el archivo temporal
        $tmp = tempnam(sys_get_temp_dir(), 'agenda_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tmp);

        return $tmp;
    }
}
