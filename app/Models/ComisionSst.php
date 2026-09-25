<?php

namespace App\Models;

use Database\Factories\ComisionSstFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'centro_trabajo_id',
    'nombre',
    'lider_id',
    'vigencia_inicio',
    'vigencia_fin',
])]
class ComisionSst extends Model
{
    /** @use HasFactory<ComisionSstFactory> */
    use HasFactory;

    protected $table = 'comisiones_sst';

    protected function casts(): array
    {
        return [
            'vigencia_inicio' => 'date',
            'vigencia_fin' => 'date',
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
    public function lider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lider_id');
    }
}
