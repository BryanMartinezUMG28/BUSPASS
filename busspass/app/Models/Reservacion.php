<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservacion extends Model
{
    // use HasFactory;
    protected $table = 'reservaciones';
    protected $primaryKey = 'id_reservacion';
// `id_usuario` int DEFAULT NULL,
//   `fecha_reservacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
//   `tipo_asiento` enum('adulto','niño','tercera_edad') DEFAULT NULL,
//   `horario` time DEFAULT NULL,
//   `numero_asiento` int DEFAULT NULL,
//   `estado` enum('confirmado','cancelado') DEFAULT 'confirmado',
//   `codigo_qr` varchar(255) DEFAULT NULL,
//   `cantidad_adultos` int DEFAULT '0',
//   `cantidad_niños` int DEFAULT '0',
//   `cantidad_tercera_edad` int DEFAULT '0',
//   `id_viaje` int NOT NULL,

    protected $fillable = [
        'id_usuario',
        'fecha_reservacion',
        'numero_asiento',
        'estado',
        'id_viaje'
    ];

    // timestamp
    public $timestamps = false;

    public function viaje()
    {
        return $this->belongsTo(Viaje::class, 'id_viaje', 'id_viaje');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'user_id');
    }
}
