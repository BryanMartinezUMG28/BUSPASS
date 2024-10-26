<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecuperacionContrasena extends Model
{
    // use HasFactory;
    protected $table = 'recuperacion_contrasena';
    protected $primaryKey = 'id_recuperacion';
    public $timestamps = false;

    // id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
    // id_usuario      INT,
    // token           VARCHAR(255) UNIQUE NOT NULL,
    // fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    // FOREIGN KEY (id_usuario) REFERENCES usuarios (user_id)
    protected $fillable = [
        'id_usuario',
        'token',
        'fecha_solicitud'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'user_id');
    }

}
