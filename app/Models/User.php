<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str; 

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // ===============================
    // 🔹 PROPIEDADES DEL MODELO
    // ===============================

    protected $fillable = [
        'name',
        'email',
        'password',
        'cargo', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    // ===============================
    // 🔹 MÉTODOS DE MUTACIÓN (Ajustado)
    // ===============================

    public function setPasswordAttribute($value)
    {
        // Esta lógica de mutación para la contraseña parece manejar si ya está hasheada.
        // Para la cédula como contraseña (temporalmente para pruebas), podrías no hashearla aquí,
        // pero para seguridad a largo plazo, ¡es fundamental hashear todas las contraseñas!
        if (!Str::startsWith($value, '$2y$') && Hash::needsRehash($value)) { // Usar \Hash::needsRehash para verificar si necesita hashearse
            $this->attributes['password'] = Hash::make($value); // Usar \Hash::make para hashear
        } else {
            $this->attributes['password'] = $value; // Si ya está hasheada o no necesita, guardar como está
        }
    }


    // ===============================
    // 🔹 EVENTOS DEL MODELO (BOOT - Eliminado o Modificado)
    // ===============================

    // Eliminamos el método boot y la lógica de asignación de roles antigua
    // ya que ahora los roles se asignan directamente a través de 'role_id'.
    // Si tienes lógica de eventos del modelo que NO esté relacionada con la asignación de roles antigua,
    // puedes mantener el método boot, pero eliminando la parte de 'static::creating' que verifica el rol 'administrador'.

}