@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <div class ="content-header">
        <div class="content-title">
            <h1>Horarios</h1>
        </div>
    </div>
    <div class = "schedule-lists">
        <div class = "career-list">

        </div>
        <div class = "docente-list">
            
        </div>
        <div class = "career-list">
            
        </div>
    </div>
    <div class = "schedule-table">

    </div>
</div>
@endsection
