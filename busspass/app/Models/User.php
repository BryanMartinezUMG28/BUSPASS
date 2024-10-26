<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // use HasFactory;
    protected $table = 'usuarios';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

//     CREATE TABLE usuarios
// (
//     id_usuario         INT AUTO_INCREMENT PRIMARY KEY,
//     nombre             VARCHAR(100)        NOT NULL,
//     correo_electronico VARCHAR(100) UNIQUE NOT NULL,
//     contrasena         VARCHAR(255)        NOT NULL,
//     direccion          TEXT,
//     numero_celular     VARCHAR(20),
//     fecha_registro     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//     email_confirmado   BOOLEAN   DEFAULT FALSE,
//     imagen_perfil      VARCHAR(255) NULL,
//     id_rol             INT                 NOT NULL,
//     FOREIGN KEY (id_rol) REFERENCES roles (id_rol)
// );

    protected $fillable = [
        'nombre',
        'correo_electronico',
        'contrasena',
        'direccion',
        'numero_celular',
        'fecha_registro',
        'fecha_actualizacion',
        'email_confirmado',
        'imagen_perfil',
        'id_rol',
        'email_token' 
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function getAuthPassword()
    {
        return $this->contrasena;
    }
    public function username()
    {
        return 'correo_electronico';
    }

    public function saveConfirmEmailToken($token)
    {
        try {
            $this->email_token = $token;
            $this->fecha_actualizacion = now();
            $this->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
