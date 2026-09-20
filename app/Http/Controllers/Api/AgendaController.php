<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AgendaBuilder;
use App\Services\AgendaXlsxExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    public function __construct(
        private readonly AgendaBuilder $builder,
        private readonly AgendaXlsxExporter $xlsxExporter,
    ) {}

    /**
     * GET /api/agenda/{day}
     * Devuelve la estructura jerárquica de la agenda del día.
     */
    public function show(Request $request, string $day): JsonResponse
    {
        $normalized = $this->normalizeDay($day);

        return response()->json([
            'data' => $this->builder->build($normalized),
        ]);
    }

    /**
     * GET /api/agenda/{day}/export/xlsx
     */
    public function exportXlsx(Request $request, string $day): BinaryFileResponse
    {
        $normalized = $this->normalizeDay($day);
        $agenda = $this->builder->build($normalized);

        $path = $this->xlsxExporter->export($agenda);
        $filename = 'Schedule '.$normalized.'.xlsx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * GET /api/agenda/{day}/export/pdf
     */
    public function exportPdf(Request $request, string $day): Response
    {
        $normalized = $this->normalizeDay($day);
        $agenda = $this->builder->build($normalized);

        $pdf = Pdf::loadView('agenda.pdf', ['agenda' => $agenda])
            ->setPaper('a3', 'landscape');

        return $pdf->download('Schedule '.$normalized.'.pdf');
    }

    private function normalizeDay(string $day): string
    {
        try {
            return Carbon::parse($day)->format('Y-m-d');
        } catch (\Throwable $e) {
            abort(422, 'Invalid date: '.$day);
        }
    }
}
