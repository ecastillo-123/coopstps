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
        Schema::create('acciones_correctivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hallazgo_id')->constrained('hallazgos')->cascadeOnDelete();
            $table->text('descripcion');
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_compromiso')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->string('estado')->default('pendiente');
            $table->string('evidencia_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accion_correctivas');
    }
};
