<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<body>

    <!-- Mensajes de sesión -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3 text-center" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3 text-center" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('index') }}">Mi Aplicación</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                {{-- Contenido que va dentro de <ul class="navbar-nav ms-auto"> --}}

{{-- Muestra el enlace de Login si el usuario NO está autenticado --}}
@guest
    <li class="nav-item">
        <a class="nav-link" href="{{ route('login') }}">Login</a>
    </li>
    {{-- Eliminamos el ejemplo de registro que estaba comentado --}}
@endguest

{{-- Muestra el nombre de usuario y el enlace de Cerrar Sesión si el usuario SÍ está autenticado --}}
@auth
    <li class="nav-item">
        {{-- Usamos <span> con clase navbar-text para texto plano --}}
        <span class="navbar-text me-2"> Bienvenido, {{ Auth::user()->name }} </span>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            Cerrar sesión
        </a>
        {{-- Este formulario oculto es necesario para la petición POST de logout --}}
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf {{-- Token CSRF para seguridad --}}
        </form>
    </li>
    {{-- Eliminamos los enlaces de dashboards y las directivas @role por ahora --}}
@endauth

{{-- Fin del contenido dentro de <ul class="navbar-nav ms-auto"> --}}
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container mt-4">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- FullCalendar -->
    <script src="{{ asset('js/fullcalendar/index.global.min.js') }}"></script>


    @stack('scripts') {{-- Para agregar scripts adicionales en vistas específicas --}}
</body>
</html>
