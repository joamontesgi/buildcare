<?php

namespace Tests\Feature;

use App\Models\Matriculado;
use App\Support\MatriculadoFields;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MatriculadoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_returns_public_fields_and_faker_for_sensitive_data(): void
    {
        Matriculado::query()->create([
            'nombre_legal' => 'NombreReal',
            'apellido1_legal' => 'ApellidoReal',
            'apellido2_legal' => 'SegundoReal',
            'documento' => '900000001',
            'papa' => '3.70',
            'promedio_actual' => '3.85',
            'cupo_creditos' => 16,
            'porcentaje_avance' => '45.50',
            'matriculas' => '7',
            'periodo_papa' => '2025-2S',
        ]);

        $response = $this->getJson('/api/matriculados');

        $response->assertOk();
        $row = $response->json('data.0');

        $this->assertEqualsCanonicalizing(
            MatriculadoFields::API_PUBLIC,
            array_keys($row)
        );

        $this->assertNotSame('NombreReal', $row['NOMBRE_LEGAL']);
        $this->assertNotSame('ApellidoReal', $row['APELLIDO1_LEGAL']);
        $this->assertNotSame('SegundoReal', $row['APELLIDO2_LEGAL']);
        $this->assertNotSame('900000001', $row['DOCUMENTO']);
        $this->assertSame('3.70', $row['PAPA']);
        $this->assertSame('3.85', $row['PROMEDIO_ACTUAL']);
        $this->assertSame(16, $row['CUPO_CREDITOS']);
        $this->assertSame('45.50', $row['PORCENTAJE_AVANCE']);
        $this->assertSame('7', $row['CANTIDAD_MATRICULAS']);
        $this->assertSame('2025-2S', $row['PERIODO_PAPA']);
    }

    public function test_post_imports_only_allowed_columns_from_excel(): void
    {
        $source = base_path('MATRICULADOS 2026-1S.xlsx');
        if (! is_file($source)) {
            $this->markTestSkipped('Archivo MATRICULADOS 2026-1S.xlsx no disponible.');
        }

        Storage::fake('local');
        $upload = new UploadedFile($source, 'matriculados.xlsx', null, null, true);

        $response = $this->postJson('/api/matriculados/import', [
            'file' => $upload,
        ]);

        $response->assertOk();
        $this->assertGreaterThan(0, $response->json('imported'));
        $this->assertGreaterThan(0, Matriculado::query()->count());

        $stored = Matriculado::query()->first();
        $this->assertNotNull($stored->acceso);
        $this->assertNotNull($stored->papa);
        $this->assertNotNull($stored->periodo_papa);
    }
}
