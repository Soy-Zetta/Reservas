<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(function (Request $request) {
            
            
            $loginCredential = $request->input('username') ?? $request->input('email'); 

            // Buscar al usuario por 'email' o por 'username' en la tabla sem_users
        
            $user = User::where('email', $loginCredential)
                        ->orWhere('username', $loginCredential)
                        ->first();

            // Si se encontró un usuario y la contraseña coincide
            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            // Si no se encuentra o la contraseña no coincide, lanzar una excepción
            throw ValidationException::withMessages([
                'username' => [trans('auth.failed')], // Mensaje de error general de autenticación
            ]);
        });


        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
