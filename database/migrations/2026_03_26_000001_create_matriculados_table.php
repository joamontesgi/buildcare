<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('matriculados', function (Blueprint $table) {
      $table->id();
      $table->string('nombre_legal')->nullable();
      $table->string('apellido1_legal')->nullable();
      $table->string('apellido2_legal')->nullable();
      $table->string('documento')->nullable();
      $table->string('acceso')->nullable();
      $table->string('subacceso')->nullable();
      $table->unsignedSmallInteger('edad')->nullable();
      $table->string('sexo_legal')->nullable();
      $table->string('genero')->nullable();
      $table->string('login')->nullable();
      $table->string('correo')->nullable();
      $table->string('estrato')->nullable();
      $table->string('pbm')->nullable();
      $table->string('matriculas')->nullable();
      $table->string('tipcolegio')->nullable();
      $table->string('ano_terminacion_colegio')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('matriculados');
  }
};
