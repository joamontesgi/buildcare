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
        Schema::table('matriculados', function (Blueprint $table) {
            $table->decimal('papa', 4, 2)->nullable()->after('matriculas');
            $table->decimal('promedio_actual', 4, 2)->nullable()->after('papa');
            $table->unsignedSmallInteger('cupo_creditos')->nullable()->after('promedio_actual');
            $table->decimal('porcentaje_avance', 5, 2)->nullable()->after('cupo_creditos');
            $table->string('periodo_papa')->nullable()->after('porcentaje_avance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matriculados', function (Blueprint $table) {
            $table->dropColumn([
                'papa',
                'promedio_actual',
                'cupo_creditos',
                'porcentaje_avance',
                'periodo_papa',
            ]);
        });
    }
};
