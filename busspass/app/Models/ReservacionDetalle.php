<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservacionDetalle extends Model
{
    // use HasFactory;
    protected $table = 'reservaciones_detalle';
    protected $primaryKey = 'id_reservacion_detalle';
    // id_reservacion         INT,
    // id_tipo_pasajero       INT,
    // cantidad_tipo_pasajero INT,

    protected $fillable = [
        'id_reservacion',
        'id_tipo_pasajero',
        'cantidad_tipo_pasajero'
    ];

    //Timestamp
    public $timestamps = false;

    public function reservacion()
    {
        return $this->belongsTo(Reservacion::class, 'id_reservacion', 'id_reservacion');
    }

    public function tipoPasajero()
    {
        return $this->belongsTo(TipoPasajero::class, 'id_tipo_pasajero', 'id_tipo_pasajero');
    }
}
