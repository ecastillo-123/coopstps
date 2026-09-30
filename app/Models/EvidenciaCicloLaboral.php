<?php

namespace App\Models;

use Database\Factories\EvidenciaCicloLaboralFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cambio_ciclo_laboral_id',
    'trabajador_id',
    'centro_trabajo_id',
    'ruta',
    'nombre_original',
    'mime_type',
    'tamano_bytes',
    'cargado_por',
])]
class EvidenciaCicloLaboral extends Model
{
    /** @use HasFactory<EvidenciaCicloLaboralFactory> */
    use HasFactory;

    protected $table = 'evidencias_ciclo_laboral';

    protected function casts(): array
    {
        return [
            'tamano_bytes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<CambioCicloLaboral, $this>
     */
    public function cambioCicloLaboral(): BelongsTo
    {
        return $this->belongsTo(CambioCicloLaboral::class);
    }

    /**
     * @return BelongsTo<Trabajador, $this>
     */
    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(Trabajador::class);
    }

    /**
     * @return BelongsTo<CentroTrabajo, $this>
     */
    public function centroTrabajo(): BelongsTo
    {
        return $this->belongsTo(CentroTrabajo::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function cargadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cargado_por');
    }
}
