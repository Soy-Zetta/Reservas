<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->

        <!-- Styles -->
        @livewireStyles
        <!-- Bootstrap y AdminLTE -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">

        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js" defer></script>


    </head>
    <body class="hold-transition sidebar-mini layout-fixed">

    <!-- Main Wrapper -->
    <div class="wrapper">





      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Menú hamburguesa -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Lado derecho de la navbar -->
 <ul class="navbar-nav ml-auto align-items-center">
    @auth
        <!-- {{-- Botón de usuarios solo en la vista de calendario --}} -->
       




        <!-- {{-- Nombre del usuario --}} -->
        @php
        use Illuminate\Support\Facades\Log;
        use Illuminate\Support\Facades\Route;

        if (auth()->check()) {
        Log::info('🧪 Usuario logueado:', [
        'id' => auth()->user()->id,
        'nombre' => auth()->user()->name,
        'rol' => auth()->user()->rol ?? 'NO DEFINIDO',
        'ruta_actual' => Route::currentRouteName(),
        ]);
        } else {
        Log::warning('⚠️ No hay usuario logueado');
        }
        @endphp

            @php
            use Illuminate\Support\Facades\Route;
            $rutaActual = Route::currentRouteName();
            @endphp

            <li class="nav-item dropdown">
            <a id="userDropdown" href="#" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <!-- {{-- Mostrar solo en la vista del calendario y si el usuario es admin --}} -->
             <div class="dropdown-item text-danger">
            RUTA: {{ $rutaActual }} <br>
            ROL: {{ auth()->user()->rol ?? 'sin rol' }}
            </div>

@if(auth()->check() && auth()->user()->isAdmin())
    <a href="{{ route('usuarios.index') }}" class="dropdown-item">
        <i class="fas fa-users-cog mr-2"></i> Gestión de Usuarios
    </a>
@endif

            {{-- Botón cerrar sesión --}}
            <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item text-danger">
            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar sesión
            </button>
            </form>
            </div>
            </li>


    @endauth
</ul>

</nav>


        <x-banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            {{-- @livewire('navigation-menu') --}}


            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                  @yield('content')
            </main>
        </div>

        @stack('modals')
        <!-- Scripts necesarios -->
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

        @livewireScripts
    </body>
</html>
