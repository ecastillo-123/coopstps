<?php

namespace App\Models;

use Database\Factories\CapacitacionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'centro_trabajo_id',
    'nombre',
    'descripcion',
    'tipo',
    'fecha_inicio',
    'fecha_fin',
    'instructor',
    'duracion_horas',
])]
class Capacitacion extends Model
{
    /** @use HasFactory<CapacitacionFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'capacitaciones';

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'duracion_horas' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<CentroTrabajo, $this>
     */
    public function centroTrabajo(): BelongsTo
    {
        return $this->belongsTo(CentroTrabajo::class);
    }
}
