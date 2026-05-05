@extends('adminlte::page')

@section('title', 'Crear Usuario')

@section('content_header')
    <h1>Crear Nuevo Usuario</h1>
@stop

@section('content')



    @if(auth()->check() && auth()->user()->email === 'informatica@colamer.edu.co')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf
                <!-- Campo oculto para params con valor por defecto -->
                <input type="hidden" name="params" value="{}">
                
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="name" class="form-control" required 
                           value="{{ old('name') }}" placeholder="Ingrese el nombre completo">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required
                           value="{{ old('email') }}" placeholder="ejemplo@dominio.com">
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" class="form-control" required
                           placeholder="Mínimo 8 caracteres">
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" required
                           placeholder="Repita la contraseña">
                </div>
                
                <div class="form-group">
                    <label>Rol</label>
                    <select name="rol" class="form-control" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('rol') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('rol')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
    @else
    <div class="alert alert-danger">
        No tienes permisos para acceder a esta sección
    </div>
    @endif
@stop