<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autobus extends Model
{
    // use HasFactory;
    protected $table = 'autobuses';
    protected $primaryKey = 'id_autobus';
    public $timestamps = false;
    public $fillable = [
       'numero_placa',
       'capacidad_asientos',
    ];
}
