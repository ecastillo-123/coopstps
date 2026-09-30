<?php

namespace App\Models;

use Database\Factories\CambioCicloLaboralFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'centro_trabajo_id',
    'trabajador_id',
    'tipo',
    'fecha_evento',
    'categoria_relacion_laboral_id',
    'registrado_por',
])]
class CambioCicloLaboral extends Model
{
    /** @use HasFactory<CambioCicloLaboralFactory> */
    use HasFactory;

    protected $table = 'cambios_ciclo_laboral';

    protected function casts(): array
    {
        return [
            'fecha_evento' => 'date',
        ];
    }

    /**
     * @return BelongsTo<CentroTrabajo, $this>
     */
    public function centroTrabajo(): BelongsTo
    {
        return $this->belongsTo(CentroTrabajo::class);
    }

    /**
     * @return BelongsTo<Trabajador, $this>
     */
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }

    /**
     * @return BelongsTo<CategoriaRelacionLaboral, $this>
     */
    public function categoriaRelacionLaboral(): BelongsTo
    {
        return $this->belongsTo(CategoriaRelacionLaboral::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * @return HasMany<EvidenciaCicloLaboral, $this>
     */
    public function evidencias(): HasMany
    {
        return $this->hasMany(EvidenciaCicloLaboral::class);
    }
}
