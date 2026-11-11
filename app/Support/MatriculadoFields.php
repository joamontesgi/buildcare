<?php

namespace App\Support;

final class MatriculadoFields
{
    /**
     * Campos expuestos en GET /api/matriculados (orden de respuesta).
     *
     * @var list<string>
     */
    public const API_PUBLIC = [
        'NOMBRE_LEGAL',
        'APELLIDO1_LEGAL',
        'APELLIDO2_LEGAL',
        'DOCUMENTO',
        'PAPA',
        'PROMEDIO_ACTUAL',
        'CUPO_CREDITOS',
        'PORCENTAJE_AVANCE',
        'CANTIDAD_MATRICULAS',
        'PERIODO_PAPA',
    ];

    /** @var list<string> Campos reemplazados por Faker en respuestas GET. */
    public const SENSITIVE = [
        'NOMBRE_LEGAL',
        'APELLIDO1_LEGAL',
        'APELLIDO2_LEGAL',
        'DOCUMENTO',
    ];

    /**
     * Cabeceras de Excel / claves de campo => atributo en base de datos.
     *
     * @var array<string, string>
     */
    public const FIELD_ATTRIBUTES = [
        'NOMBRE_LEGAL' => 'nombre_legal',
        'APELLIDO1_LEGAL' => 'apellido1_legal',
        'APELLIDO2_LEGAL' => 'apellido2_legal',
        'DOCUMENTO' => 'documento',
        'ACCESO' => 'acceso',
        'SUBACCESO' => 'subacceso',
        'EDAD' => 'edad',
        'SEXO_LEGAL' => 'sexo_legal',
        'GENERO' => 'genero',
        'LOGIN' => 'login',
        'CORREO' => 'correo',
        'ESTRATO' => 'estrato',
        'PBM' => 'pbm',
        'MATRICULAS' => 'matriculas',
        'CANTIDAD_MATRICULAS' => 'matriculas',
        'TIPCOLEGIO' => 'tipcolegio',
        'ANO_TERMINACION_COLEGIO' => 'ano_terminacion_colegio',
        'PAPA' => 'papa',
        'PROMEDIO_ACTUAL' => 'promedio_actual',
        'CUPO_CREDITOS' => 'cupo_creditos',
        'PORCENTAJE_AVANCE' => 'porcentaje_avance',
        'PERIODO_PAPA' => 'periodo_papa',
    ];

    /**
     * Sinónimos de cabecera en Excel => clave en FIELD_ATTRIBUTES.
     *
     * @var array<string, string>
     */
    public const IMPORT_ALIASES = [
        'PCT_AVANCE' => 'PORCENTAJE_AVANCE',
        'PORCENTAJE_DE_AVANCE' => 'PORCENTAJE_AVANCE',
        'CUPO_DE_CREDITOS' => 'CUPO_CREDITOS',
    ];

    /**
     * @return array<string, string> Cabecera normalizada => atributo del modelo
     */
    public static function headerToAttributeMap(): array
    {
        $map = [];

        foreach (self::FIELD_ATTRIBUTES as $header => $attribute) {
            $map[self::normalizeHeader($header)] = $attribute;
        }

        foreach (self::IMPORT_ALIASES as $header => $field) {
            $map[self::normalizeHeader($header)] = self::fieldToAttribute($field);
        }

        return $map;
    }

    public static function normalizeHeader(string $header): string
    {
        $header = trim($header);
        $header = preg_replace('/\s+/', '_', $header) ?? $header;

        return strtoupper($header);
    }

    public static function fieldToAttribute(string $field): string
    {
        if (! isset(self::FIELD_ATTRIBUTES[$field])) {
            throw new \InvalidArgumentException("Campo desconocido: {$field}");
        }

        return self::FIELD_ATTRIBUTES[$field];
    }

    /**
     * @return array<string, null>
     */
    public static function emptyAttributes(): array
    {
        $row = [];
        foreach (array_unique(array_values(self::FIELD_ATTRIBUTES)) as $attribute) {
            $row[$attribute] = null;
        }

        return $row;
    }
}
