<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Muestra la vista del login.
     */
    public function showLoginForm()
    {
        
        return view('auth.login');
    }

    /**
     * Procesa el login del usuario.
     */
          public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        
        
        $user = \App\Models\User::where('email', trim($request->email))->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No se encontró un usuario con este email.'])->withInput();
        }

      

        // Intenta autenticar al usuario con Auth::attempt().
        // Este método devolverá true si las credenciales son válidas.
        if (Auth::attempt(['email' => strtolower(trim($request->email)), 'password' => $request->password])) {

        
            // Regenera la sesión para prevenir ataques de fijación de sesión.
            $request->session()->regenerate();
    
            // Redirige al usuario a la URL deseada, por ejemplo '/home'.
            return redirect()->route('index') -> with('success', '¡Bienvenido, ' . Auth::user()->name . '!');
        }

        // Si la autenticación falla, regresa al formulario de login con un error.
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput();
    }

    /**
     * Método adicional para redirigir tras autenticación exitosa.
     * (Este método ya no es estrictamente necesario si usas redirect()->intended() en el login.)
     */
    public function authenticated(Request $request, $user)
    {
        return redirect('/');
    }

    public function logout(Request $request)
{
    Auth::logout(); // Cierra la sesión del usuario

    $request->session()->invalidate(); // Invalida la sesión
    $request->session()->regenerateToken(); // Regenera el token CSRF

    return redirect('/')->with('success', 'Sesión cerrada correctamente.');
}
    



}
