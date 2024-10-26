<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    // use HasFactory;
    protected $table = 'notificaciones';
    protected $primaryKey = 'id_notificacion ';
    // id_notificacion   INT AUTO_INCREMENT PRIMARY KEY,
    // id_usuario        INT,
    // tipo_notificacion ENUM ('cambio_viaje', 'recordatorio'),
    // mensaje           TEXT,
    // fecha_envio       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    // FOREIGN KEY (id_usuario) REFERENCES usuarios (user_id)
    protected $fillable = [
        'id_usuario',
        'tipo_notificacion',
        'mensaje',
        'fecha_envio',
    ];
    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'user_id');
    }

}
