<?php

namespace App\Support;

final class MatriculadoFields
{
  /** @var list<string> Columnas permitidas (cabeceras del Excel / JSON). */
  public const ALLOWED = [
    'NOMBRE_LEGAL',
    'APELLIDO1_LEGAL',
    'APELLIDO2_LEGAL',
    'DOCUMENTO',
    'ACCESO',
    'SUBACCESO',
    'EDAD',
    'SEXO_LEGAL',
    'GENERO',
    'LOGIN',
    'CORREO',
    'ESTRATO',
    'PBM',
    'MATRICULAS',
    'TIPCOLEGIO',
    'ANO_TERMINACION_COLEGIO',
  ];

  /** @var list<string> Campos reemplazados por Faker en respuestas GET. */
  public const SENSITIVE = [
    'NOMBRE_LEGAL',
    'APELLIDO1_LEGAL',
    'APELLIDO2_LEGAL',
    'DOCUMENTO',
  ];

  /**
   * @return array<string, string> Cabecera normalizada => atributo del modelo
   */
  public static function headerToAttributeMap(): array
  {
    $map = [];
    foreach (self::ALLOWED as $field) {
      $map[self::normalizeHeader($field)] = self::toAttribute($field);
    }

    return $map;
  }

  public static function normalizeHeader(string $header): string
  {
    $header = trim($header);
    $header = preg_replace('/\s+/', '_', $header) ?? $header;

    return strtoupper($header);
  }

  public static function toAttribute(string $field): string
  {
    return strtolower($field);
  }

  /**
   * @return array<string, null>
   */
  public static function emptyAttributes(): array
  {
    $row = [];
    foreach (self::ALLOWED as $field) {
      $row[self::toAttribute($field)] = null;
    }

    return $row;
  }
}
