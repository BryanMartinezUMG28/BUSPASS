<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
    // use HasFactory;
    protected $table = 'boletos';
    protected $primaryKey = 'id_boleto';
    public $timestamps = false;
    // id_reservacion INT,
    // fecha_compra   TIMESTAMP                    DEFAULT CURRENT_TIMESTAMP,
    // metodo_pago    ENUM ('tarjeta', 'sucursal') NOT NULL,
    // estado_pago    ENUM ('pagado', 'pendiente') DEFAULT 'pendiente',
    // FOREIGN KEY (id_reservacion) REFERENCES reservaciones (id_reservacion)

    protected $fillable = [
        'id_reservacion',
        'fecha_compra',
        'metodo_pago',
        'estado_pago',
        'boleto_cancelado'
    ];

    public function reservacion()
    {
        return $this->belongsTo(Reservacion::class, 'id_reservacion', 'id_reservacion');
    }
}
