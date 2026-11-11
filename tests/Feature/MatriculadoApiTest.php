<?php

namespace Tests\Feature;

use App\Models\Matriculado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MatriculadoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_returns_faker_for_sensitive_fields(): void
    {
        Matriculado::query()->create([
            'nombre_legal' => 'NombreReal',
            'apellido1_legal' => 'ApellidoReal',
            'apellido2_legal' => 'SegundoReal',
            'documento' => '900000001',
            'acceso' => 'ORDINARIO',
            'subacceso' => 'REGULAR',
            'edad' => 20,
            'sexo_legal' => 'F',
            'genero' => 'FEMENINO',
            'login' => 'estudiante1',
            'correo' => 'real@uni.edu.co',
            'estrato' => '2',
            'pbm' => '72',
            'matriculas' => '1',
            'tipcolegio' => 'OFICIAL',
            'ano_terminacion_colegio' => '2020',
        ]);

        $response = $this->getJson('/api/matriculados');

        $response->assertOk();
        $row = $response->json('data.0');

        $this->assertNotSame('NombreReal', $row['NOMBRE_LEGAL']);
        $this->assertNotSame('ApellidoReal', $row['APELLIDO1_LEGAL']);
        $this->assertNotSame('SegundoReal', $row['APELLIDO2_LEGAL']);
        $this->assertNotSame('900000001', $row['DOCUMENTO']);
        $this->assertSame('ORDINARIO', $row['ACCESO']);
        $this->assertSame('estudiante1', $row['LOGIN']);
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
        $this->assertNull($stored->nombre_legal);
    }
}
