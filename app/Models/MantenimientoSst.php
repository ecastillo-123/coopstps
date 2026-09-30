<?php

namespace App\Models;

use Database\Factories\MantenimientoSstFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'centro_trabajo_id',
    'equipo',
    'tipo',
    'frecuencia',
    'ultima_fecha',
    'proxima_fecha',
    'responsable_id',
])]
class MantenimientoSst extends Model
{
    /** @use HasFactory<MantenimientoSstFactory> */
    use HasFactory;

    protected $table = 'mantenimiento_sst';

    protected function casts(): array
    {
        return [
            'ultima_fecha' => 'date',
            'proxima_fecha' => 'date',
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
     * @return BelongsTo<User, $this>
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
