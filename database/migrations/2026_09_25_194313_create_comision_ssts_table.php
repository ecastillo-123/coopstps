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
        Schema::create('comisiones_sst', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_trabajo_id')->constrained('centros_trabajo')->cascadeOnDelete();
            $table->string('nombre');
            $table->foreignId('lider_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comision_ssts');
    }
};
