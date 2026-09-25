<?php

namespace App\Models;

use Database\Factories\NomReglaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'codigo',
    'titulo',
    'descripcion',
    'fuente',
])]
class NomRegla extends Model
{
    /** @use HasFactory<NomReglaFactory> */
    use HasFactory;

    protected $table = 'nom_reglas';
}
