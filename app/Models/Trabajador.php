<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'centro_trabajo_id',
    'puesto_id',
    'numero_empleado',
    'nombre',
    'apellido_paterno',
    'apellido_materno',
    'curp',
    'nss',
    'rfc',
    'fecha_nacimiento',
    'fecha_ingreso',
    'activo',
])]
class Trabajador extends Model
{
    use HasFactory;

    protected $table = 'trabajadores';

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function centroTrabajo(): BelongsTo
    {
        return $this->belongsTo(CentroTrabajo::class);
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(ContratoTrabajador::class);
    }

    /**
     * @return HasMany<CambioCicloLaboral, $this>
     */
    public function cambiosCicloLaboral(): HasMany
    {
        return $this->hasMany(CambioCicloLaboral::class);
    }
}
