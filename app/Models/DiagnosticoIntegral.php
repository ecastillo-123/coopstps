<?php

namespace App\Models;

use Database\Factories\DiagnosticoIntegralFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'centro_trabajo_id',
    'titulo',
    'descripcion',
    'area',
    'fecha',
    'responsable_id',
])]
class DiagnosticoIntegral extends Model
{
    /** @use HasFactory<DiagnosticoIntegralFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'diagnosticos_integrales';

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
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
