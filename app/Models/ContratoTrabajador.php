<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'trabajador_id',
    'plantilla_contrato_id',
    'modalidad_contrato_id',
    'categoria_relacion_laboral_id',
    'fecha_inicio',
    'fecha_fin',
    'salario',
    'activo',
])]
class ContratoTrabajador extends Model
{
    use HasFactory;

    protected $table = 'contratos_trabajador';

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'salario' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function plantillaContrato(): BelongsTo
    {
        return $this->belongsTo(PlantillaContrato::class);
    }

    public function modalidadContrato(): BelongsTo
    {
        return $this->belongsTo(ModalidadContrato::class);
    }

    public function categoriaRelacionLaboral(): BelongsTo
    {
        return $this->belongsTo(CategoriaRelacionLaboral::class);
    }
}
