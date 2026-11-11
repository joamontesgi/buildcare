<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matriculado extends Model
{
  use HasFactory;

  protected $fillable = [
    'nombre_legal',
    'apellido1_legal',
    'apellido2_legal',
    'documento',
    'acceso',
    'subacceso',
    'edad',
    'sexo_legal',
    'genero',
    'login',
    'correo',
    'estrato',
    'pbm',
    'matriculas',
    'papa',
    'promedio_actual',
    'cupo_creditos',
    'porcentaje_avance',
    'periodo_papa',
    'tipcolegio',
    'ano_terminacion_colegio',
  ];

  protected $casts = [
    'edad' => 'integer',
    'papa' => 'decimal:2',
    'promedio_actual' => 'decimal:2',
    'cupo_creditos' => 'integer',
    'porcentaje_avance' => 'decimal:2',
  ];
}
