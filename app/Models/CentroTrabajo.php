<?php

namespace App\Models;

use Database\Factories\CentroTrabajoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * @return HasMany<Trabajador, $this>
     */
    public function trabajadores(): HasMany
    {
        return $this->hasMany(Trabajador::class);
    }

    /**
     * @return HasMany<Hallazgo, $this>
     */
    public function hallazgos(): HasMany
    {
        return $this->hasMany(Hallazgo::class);
    }

    /**
     * @return HasMany<Capacitacion, $this>
     */
    public function capacitaciones(): HasMany
    {
        return $this->hasMany(Capacitacion::class);
    }

    /**
     * @return HasMany<Inspeccion, $this>
     */
    public function inspecciones(): HasMany
    {
        return $this->hasMany(Inspeccion::class);
    }

    /**
     * @return HasMany<Auditoria, $this>
     */
    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class);
    }

    /**
     * @return HasMany<DiagnosticoIntegral, $this>
     */
    public function diagnosticosIntegrales(): HasMany
    {
        return $this->hasMany(DiagnosticoIntegral::class);
    }
}
