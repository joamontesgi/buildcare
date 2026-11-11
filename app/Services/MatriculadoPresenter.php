<?php

namespace App\Services;

use App\Models\Matriculado;
use App\Support\MatriculadoFields;
use Faker\Factory;
use Faker\Generator;

class MatriculadoPresenter
{
  /**
   * @return array<string, mixed>
   */
  public function toPublicArray(Matriculado $matriculado): array
  {
    $faker = $this->fakerFor($matriculado->id);

    $row = [];
    foreach (MatriculadoFields::ALLOWED as $field) {
      $attribute = MatriculadoFields::toAttribute($field);

      if (in_array($field, MatriculadoFields::SENSITIVE, true)) {
        $row[$field] = $this->fakeSensitive($field, $faker);

        continue;
      }

      $row[$field] = $matriculado->{$attribute};
    }

    return $row;
  }

  private function fakerFor(int $seed): Generator
  {
    $faker = Factory::create(config('app.faker_locale', 'es_CO'));
    $faker->seed($seed * 1009);

    return $faker;
  }

  private function fakeSensitive(string $field, Generator $faker): string
  {
    return match ($field) {
      'NOMBRE_LEGAL' => $faker->firstName(),
      'APELLIDO1_LEGAL' => $faker->lastName(),
      'APELLIDO2_LEGAL' => $faker->lastName(),
      'DOCUMENTO' => (string) $faker->numberBetween(10_000_000, 99_999_999_999),
      default => $faker->word(),
    };
  }
}
