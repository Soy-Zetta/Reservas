<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, HasTeams, Notifiable, TwoFactorAuthenticatable, HasRoles;

    
    // Nombre de la tabla
    protected $table = 'users';
    public $timestamps = false;
    // Atributos que se pueden asignar masivamente
    protected $fillable = [
        'username',  // Nombre de usuario
        'email',
        'password',
    ];

    // Atributos que deben ser ocultos al serializar
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    // Atributos adicionales que se agregan a la respuesta del modelo
    protected $appends = [
        'profile_photo_url',
    ];

    // Campos que deben ser convertidos a tipos específicos
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

