@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.calendario') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver al Calendario
    </a>

    <span class="text-dark font-weight-bold">
        <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
    </span>
</div>

@if(auth()->check() && auth()->user()->email === 'informatica@colamer.edu.co')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4 text-primary">Gestión de Usuarios</h2>

            {{-- Mostrar mensajes de éxito --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Formulario de creación --}}
            @if (auth()->user()->rol === 'admin')
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Crear nuevo usuario</h3>
                    </div>
                    <form action="{{ route('usuarios.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Nombre</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Correo</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Rol</label>
                                    <select name="rol" class="form-control" required>
                                        <option value="user">Usuario</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Contraseña</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Confirmar contraseña</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Crear usuario</button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Tabla de usuarios --}}
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h3 class="card-title">Lista de usuarios</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                @if (auth()->user()->rol === 'admin')
                                    <th class="text-center">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge badge-{{ $user->rol === 'admin' ? 'danger' : 'secondary' }}">{{ ucfirst($user->rol) }}</span></td>
                                    @if (auth()->user()->rol === 'admin')
                                        <td class="text-center">
                                            <form action="{{ route('usuarios.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este usuario?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">Eliminar</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-danger">
    No tienes permisos para acceder a esta sección
</div>
@endif
@endsection