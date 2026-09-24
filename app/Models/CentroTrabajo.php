<?php

namespace App\Models;

use Database\Factories\CentroTrabajoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'clave',
    'nombre',
    'numero_registro_patronal',
    'rfc',
    'estado',
    'ciudad',
    'direccion',
    'telefono',
    'email',
    'activo',
])]
class CentroTrabajo extends Model
{
    /** @use HasFactory<CentroTrabajoFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'centros_trabajo';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
