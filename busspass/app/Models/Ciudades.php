<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudades extends Model
{
    // use HasFactory;
    protected $table = 'ciudades';
    protected $primaryKey = 'id_ciudad';
    public $timestamps = false;
    // nombre      VARCHAR(100) NOT NULL,
    // estado      VARCHAR(100) NULL,
    // pais        VARCHAR(100) NULL,

    protected $fillable = [
        'nombre',
        'departamento',
    ];
}
