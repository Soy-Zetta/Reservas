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
    use HasApiTokens; 
    use HasFactory; 
    use HasProfilePhoto; 
    use HasTeams; 
    use Notifiable;
    use TwoFactorAuthenticatable; 
    use HasRoles;

    protected $table = 'users'; // Laravel usará sem_users por el prefix
    protected $guard_name = 'web';
    public $timestamps = false; // ✅ DESACTIVA los campos created_at / updated_at

protected $fillable = [
    'name',
    'username',
    'email',
    'password',
    'rol',
    'params',
    'registerDate',
    'lastvisitDate',
    'lastResetTime'  // Añade este campo
];

protected $attributes = [
    'params' => '{}',
    'registerDate' => null,
    'lastvisitDate' => null,
    'lastResetTime' => null  // Añade este campo
];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'registerDate' => 'datetime',
        'lastvisitDate' => 'datetime',
        'lastResetTime' => 'datetime'  // Añade este cast
    ];
}
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->registerDate = $model->registerDate ?? now();
            $model->lastvisitDate = $model->lastvisitDate ?? now();  // Nueva línea añadida
            $model->lastResetTime = $model->lastResetTime ?? now(); 
        });
    }

public function isAdmin()
{
    // Verifica ambos sistemas de roles por seguridad
    return $this->rol === 'admin' || $this->hasRole('admin');
}

    public function adminlte_desc()
    {
        return $this->isAdmin() ? 'Administrador' : 'Usuario';
    }
}