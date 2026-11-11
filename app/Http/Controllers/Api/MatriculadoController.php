<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matriculado;
use App\Services\MatriculadoExcelImporter;
use App\Services\MatriculadoPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatriculadoController extends Controller
{
  public function index(MatriculadoPresenter $presenter): JsonResponse
  {
    $data = Matriculado::query()
      ->orderBy('id')
      ->get()
      ->map(fn (Matriculado $row) => $presenter->toPublicArray($row))
      ->values();

    return response()->json(['data' => $data]);
  }

  public function import(Request $request, MatriculadoExcelImporter $importer): JsonResponse
  {
    $validated = $request->validate([
      'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:51200'],
    ]);

    $path = $validated['file']->getRealPath();
    if ($path === false) {
      return response()->json(['message' => 'No se pudo leer el archivo subido.'], 422);
    }

    try {
      $imported = $importer->import($path);
    } catch (\InvalidArgumentException $e) {
      return response()->json(['message' => $e->getMessage()], 422);
    }

    return response()->json([
      'message' => 'Importación completada.',
      'imported' => $imported,
    ]);
  }
}
