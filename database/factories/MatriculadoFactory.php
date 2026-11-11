<?php

namespace Database\Factories;

use App\Models\Matriculado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matriculado>
 */
class MatriculadoFactory extends Factory
{
    protected $model = Matriculado::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sexo = fake()->randomElement(['M', 'F']);
        $genero = $sexo === 'M' ? 'MASCULINO' : 'FEMENINO';
        $documento = (string) fake()->unique()->numberBetween(10_000_000, 99_999_999_999);
        $login = 'u'.substr($documento, -8);
        $papa = fake()->randomFloat(2, 0, 5);
        $cupo = fake()->numberBetween(8, 22);
        $avance = fake()->randomFloat(2, 5, 100);

        return [
            'nombre_legal' => fake()->firstName($sexo === 'M' ? 'male' : 'female'),
            'apellido1_legal' => fake()->lastName(),
            'apellido2_legal' => fake()->lastName(),
            'documento' => $documento,
            'acceso' => fake()->randomElement(['ORDINARIO', 'ESPECIAL', 'REINGRESO', 'TRANSFERENCIA']),
            'subacceso' => fake()->randomElement(['REGULAR', 'NIVELATORIO', 'PRIMERA VEZ']),
            'edad' => fake()->numberBetween(16, 45),
            'sexo_legal' => $sexo,
            'genero' => $genero,
            'login' => $login,
            'correo' => $login.'@unal.edu.co',
            'estrato' => (string) fake()->numberBetween(1, 6),
            'pbm' => (string) fake()->numberBetween(0, 100),
            'matriculas' => (string) fake()->numberBetween(1, 12),
            'papa' => $papa,
            'promedio_actual' => fake()->randomFloat(2, 0, 5),
            'cupo_creditos' => $cupo,
            'porcentaje_avance' => $avance,
            'periodo_papa' => fake()->randomElement(['2024-2S', '2025-1S', '2025-2S']),
            'tipcolegio' => fake()->randomElement(['OFICIAL', 'PRIVADO', 'OTRO']),
            'ano_terminacion_colegio' => (string) fake()->numberBetween(2010, 2025),
        ];
    }
}
