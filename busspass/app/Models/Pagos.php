<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagos extends Model
{
    // use HasFactory;
    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';

    // id_boleto   INT,
    // fecha_pago  TIMESTAMP                        DEFAULT CURRENT_TIMESTAMP,
    // cantidad    DECIMAL(10, 2),
    // metodo_pago ENUM ('tarjeta', 'sucursal'),
    // estado      ENUM ('confirmado', 'pendiente') DEFAULT 'pendiente',
    // FOREIGN KEY (id_boleto) REFERENCES boletos (id_boleto)
    protected $fillable = [
        'id_boleto',
        'fecha_pago',
        'cantidad',
        'metodo_pago',
        'estado'
    ];
    //timestamps
    public $timestamps = false;

    public function boleto()
    {
        return $this->belongsTo(Boleto::class, 'id_boleto', 'id_boleto');
    }
}
