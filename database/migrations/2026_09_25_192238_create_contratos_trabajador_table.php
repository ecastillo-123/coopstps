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
        Schema::create('contratos_trabajador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->cascadeOnDelete();
            $table->foreignId('plantilla_contrato_id')->constrained('plantillas_contrato')->restrictOnDelete();
            $table->foreignId('modalidad_contrato_id')->constrained('modalidades_contrato')->restrictOnDelete();
            $table->foreignId('categoria_relacion_laboral_id')->constrained('categorias_relacion_laboral')->restrictOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->decimal('salario', 12, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos_trabajador');
    }
};
