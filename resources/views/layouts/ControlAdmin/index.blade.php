@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <h1 class="page-title">Control Administrativo</h1>
    <a href="{{ route('ControlAdmin.carrers') }}">Carreras</a>
    <a href="{{ route('Listas.students.index') }}">Lista de Alumnos</a>
    <a href="{{ route('Listas.members.index') }}">Lista de Docentes</a>
    <a href="{{ route('Listas.users.index') }}">Listas de usarios</a>
    <a href="{{ route('Listas.materias.index') }}">Lista de materias</a>
    <a href="{{ route('ControlAdmin.users') }}">Usarios</a>
</div>
@endsection
