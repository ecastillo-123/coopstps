<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['clave', 'nombre', 'contenido', 'activo'])]
class PlantillaContrato extends Model
{
    use HasFactory;

    protected $table = 'plantillas_contrato';

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(ContratoTrabajador::class);
    }
}
