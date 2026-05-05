@extends('adminlte::page')

@section('title', 'Listado de Usuarios')

@section('css')
    @livewireStyles
@stop

@section('content_header')
    <h1>Listado de Usuarios</h1>
@stop

@section('content')
    @livewire('admin.users-index')
@stop

@section('js')
    <script src="{{ asset('vendor/livewire/livewire.js') }}" data-update-uri="{{ url('/livewire/update') }}" data-navigate-once="true"></script>
@stop