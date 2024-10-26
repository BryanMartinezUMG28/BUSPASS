<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viaje extends Model
{
    // use HasFactory;
    protected $table = 'viajes';
    protected $primaryKey = 'id_viaje';
    public $timestamps = false;
    // origen             VARCHAR(100),
    // destino            VARCHAR(100),
    // horario            TIME,
    // frecuencia         INT,
    // id_autobus         INT,
    // capacidad_asientos INT,
    // politica_especial  TEXT

    protected $fillable = [
        'origen',
        'destino',
        'horario',
        'frecuencia',
        'id_autobus',
        'capacidad_asientos',
        'politica_especial',
        'precio_asiento_adulto'
    ];

    public function autobus()
    {
        return $this->belongsTo(Autobus::class, 'id_autobus', 'id_autobus');
    }
}
