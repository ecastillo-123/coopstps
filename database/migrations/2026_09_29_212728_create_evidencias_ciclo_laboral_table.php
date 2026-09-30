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
        Schema::create('evidencias_ciclo_laboral', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cambio_ciclo_laboral_id')
                ->constrained('cambios_ciclo_laboral')
                ->restrictOnDelete();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->restrictOnDelete();
            $table->foreignId('centro_trabajo_id')->constrained('centros_trabajo')->restrictOnDelete();
            $table->string('ruta');
            $table->string('nombre_original');
            $table->string('mime_type', 127);
            $table->unsignedBigInteger('tamano_bytes');
            $table->foreignId('cargado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('ruta');
            $table->index(['centro_trabajo_id', 'trabajador_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidencias_ciclo_laboral');
    }
};
