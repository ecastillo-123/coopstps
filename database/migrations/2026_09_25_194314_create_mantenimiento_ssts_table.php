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
        Schema::create('mantenimiento_sst', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_trabajo_id')->constrained('centros_trabajo')->cascadeOnDelete();
            $table->string('equipo');
            $table->string('tipo');
            $table->string('frecuencia')->nullable();
            $table->date('ultima_fecha')->nullable();
            $table->date('proxima_fecha')->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_ssts');
    }
};
