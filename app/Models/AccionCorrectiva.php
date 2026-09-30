<?php

namespace App\Models;

use Database\Factories\AccionCorrectivaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hallazgo_id',
    'descripcion',
    'responsable_id',
    'fecha_compromiso',
    'fecha_cierre',
    'estado',
    'evidencia_url',
])]
class AccionCorrectiva extends Model
{
    /** @use HasFactory<AccionCorrectivaFactory> */
    use HasFactory;

    protected $table = 'acciones_correctivas';

    protected function casts(): array
    {
        return [
            'fecha_compromiso' => 'date',
            'fecha_cierre' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Hallazgo, $this>
     */
    public function hallazgo(): BelongsTo
    {
        return $this->belongsTo(Hallazgo::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
