<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Redirige a esta ruta después de un restablecimiento de contraseña exitoso.
     *
     * @var string
     */
    protected $redirectTo = '/home';
}
