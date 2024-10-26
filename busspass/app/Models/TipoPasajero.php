<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPasajero extends Model
{
    // use HasFactory;
    protected $table = 'tipo_pasajero';
    protected $primaryKey = 'id_tipo_pasajero';

    protected $fillable = [
       'nombre'
    ];
    // timestamp
    public $timestamps = false;
}
