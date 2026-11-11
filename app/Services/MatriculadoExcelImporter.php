<?php

namespace App\Services;

use App\Models\Matriculado;
use App\Support\MatriculadoFields;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MatriculadoExcelImporter
{
  /**
   * Reemplaza todos los registros por los del archivo (solo columnas permitidas).
   */
  public function import(string $path): int
  {
    $spreadsheet = IOFactory::load($path);
    $sheet = $this->resolveSheet($spreadsheet);
    $columnMap = $this->buildColumnMap($sheet);

    if ($columnMap === []) {
      throw new \InvalidArgumentException('No se encontraron columnas permitidas en la primera fila del Excel.');
    }

    Matriculado::query()->delete();

    $count = 0;
    $batch = [];
    $highestRow = (int) $sheet->getHighestRow();

    for ($row = 2; $row <= $highestRow; $row++) {
      $attributes = MatriculadoFields::emptyAttributes();

      foreach ($columnMap as $columnIndex => $attribute) {
        $attributes[$attribute] = $this->normalizeValue(
          $attribute,
          $sheet->getCellByColumnAndRow($columnIndex, $row)->getFormattedValue()
        );
      }

      if ($this->isBlankRow($attributes)) {
        continue;
      }

      $batch[] = array_merge($attributes, [
        'created_at' => now(),
        'updated_at' => now(),
      ]);
      $count++;

      if (count($batch) >= 250) {
        Matriculado::insert($batch);
        $batch = [];
      }
    }

    if ($batch !== []) {
      Matriculado::insert($batch);
    }

    return $count;
  }

  private function resolveSheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): Worksheet
  {
    $best = $spreadsheet->getActiveSheet();
    $bestRows = 0;

    foreach ($spreadsheet->getAllSheets() as $worksheet) {
      $rows = (int) $worksheet->getHighestRow();
      if ($rows > $bestRows) {
        $bestRows = $rows;
        $best = $worksheet;
      }
    }

    return $best;
  }

  /**
   * @return array<int, string> índice de columna (1-based) => atributo del modelo
   */
  private function buildColumnMap(Worksheet $sheet): array
  {
    $attributeByHeader = MatriculadoFields::headerToAttributeMap();
    $maxCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
    $map = [];

    for ($col = 1; $col <= $maxCol; $col++) {
      $header = MatriculadoFields::normalizeHeader((string) $sheet->getCellByColumnAndRow($col, 1)->getValue());
      if ($header === '') {
        continue;
      }

      if (isset($attributeByHeader[$header])) {
        $map[$col] = $attributeByHeader[$header];
      }
    }

    return $map;
  }

  /**
   * @param  array<string, mixed>  $attributes
   */
  private function isBlankRow(array $attributes): bool
  {
    foreach ($attributes as $value) {
      if ($value !== null && $value !== '') {
        return false;
      }
    }

    return true;
  }

  private function normalizeValue(string $attribute, mixed $value): mixed
  {
    if ($value === null) {
      return null;
    }

    $value = is_string($value) ? trim($value) : $value;

    if ($value === '') {
      return null;
    }

    if ($attribute === 'edad') {
      return is_numeric($value) ? (int) $value : null;
    }

    return is_scalar($value) ? (string) $value : null;
  }
}
