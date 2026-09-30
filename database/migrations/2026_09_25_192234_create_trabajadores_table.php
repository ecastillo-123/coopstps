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
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('centro_trabajo_id')->constrained('centros_trabajo')->cascadeOnDelete();
            $table->foreignId('puesto_id')->nullable()->constrained('puestos')->nullOnDelete();
            $table->string('numero_empleado')->nullable();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('curp', 18)->nullable();
            $table->string('nss', 20)->nullable();
            $table->string('rfc', 13)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_ingreso');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['centro_trabajo_id', 'numero_empleado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajadores');
    }
};
