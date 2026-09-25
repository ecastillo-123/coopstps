<?php

namespace App\Models;

use Database\Factories\HallazgoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'centro_trabajo_id',
    'trabajador_id',
    'titulo',
    'descripcion',
    'riesgo',
    'estado',
    'tipo',
    'referencia_nom',
    'detected_at',
])]
class Hallazgo extends Model
{
    /** @use HasFactory<HallazgoFactory> */
    use HasFactory;

    protected $table = 'hallazgos';

    protected function casts(): array
    {
        return [
            'detected_at' => 'date',
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
     * @return HasMany<AccionCorrectiva, $this>
     */
    public function acciones(): HasMany
    {
        return $this->hasMany(AccionCorrectiva::class);
    }
}
