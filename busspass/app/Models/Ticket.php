<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    // use HasFactory;
    protected $table = 'reservaciones';
    protected $primaryKey = 'id_reservacion';
    // id_reservacion    INT AUTO_INCREMENT PRIMARY KEY,
    // id_usuario        INT,
    // fecha_reservacion TIMESTAMP                        DEFAULT CURRENT_TIMESTAMP,
    // origen            VARCHAR(100),
    // destino           VARCHAR(100),
    // tipo_asiento      ENUM ('adulto', 'niño', 'tercera_edad'),
    // horario           TIME,
    // numero_asiento    INT,
    // estado            ENUM ('confirmado', 'cancelado') DEFAULT 'confirmado',
    // codigo_qr         VARCHAR(255),
    // FOREIGN KEY (id_usuario) REFERENCES usuarios (user_id)

    protected $fillable = [
        'id_usuario',
        'fecha_reservacion',
        'origen',
        'destino',
        'tipo_asiento',
        'horario',
        'numero_asiento',
        'estado',
        'codigo_qr',
    ];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'user_id');
    }
}
