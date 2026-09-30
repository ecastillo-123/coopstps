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
        Schema::create('hallazgos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_trabajo_id')->constrained('centros_trabajo')->cascadeOnDelete();
            $table->foreignId('trabajador_id')->nullable()->constrained('trabajadores')->nullOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('riesgo');
            $table->string('estado')->default('abierto');
            $table->string('tipo')->nullable();
            $table->string('referencia_nom')->nullable();
            $table->date('detected_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hallazgos');
    }
};
