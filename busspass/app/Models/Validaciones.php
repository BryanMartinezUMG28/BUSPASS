<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Validaciones extends Model
{
    // use HasFactory;
    protected $table = 'validaciones';
    protected $primaryKey = 'id_validacion';
    //     `id_boleto` int DEFAULT NULL,
    //   `fecha_validacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    //   `estado_ticket` enum('válido','caducado','cancelado')
    protected $fillable = [
        'id_boleto',
        'fecha_validacion',
        'estado_ticket'
    ];
    public $timestamps = false;
    public function boleto()
    {
        return $this->belongsTo(Boleto::class, 'id_boleto');
    }
}
