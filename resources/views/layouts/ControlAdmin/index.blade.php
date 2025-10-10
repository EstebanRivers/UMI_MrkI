@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <h1 class="page-title">Control Administrativo</h1>

</div>
@endsection
