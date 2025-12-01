@extends('layouts.app')

@section('title', 'Cursos - ' . session('active_institution_name'))

@vite(['resources/css/ControlEsc/base.css','resources/js/app.js'])

@section('content')
    {{-- Contenedor principal del formulario --}}
    <div class="form-container">
        {{-- Encabezado --}}
        <div class="header-section">
            <h2 class="form-title">Registro de Docente</h2>
        </div>
        
        {{-- Cuerpo del formulario --}}
        <div class="form-body">
            <form method="POST" action="{{ route('control.teachers.store') }}" class="registration-form">
                @csrf
                
                {{-- Bloque para mostrar mensajes Flash (éxito o error) --}}
                @if (session('success'))
                    <div class="message-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    {{-- Esta es la sección que necesitas para ver el error del bloque catch --}}
                    <div class="message-error" style="border: 1px solid red; padding: 10px; margin-bottom: 15px; background: #ffe0e0;">
                        <strong>¡Error en el servidor!</strong> {{ session('error') }}
                    </div>
                @endif

                {{-- Sección: Datos Personales --}}
                <h3>👤 Datos Personales</h3>
                <hr>
                <div class="form-group-triple">
                    {{-- Nombre(s) --}}
                    <div class="form-field">
                        <label for="nombre">Nombre(s)</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    </div>

                    {{-- Apellido Paterno --}}
                    <div class="form-field">
                        <label for="apellido_paterno">Apellido Paterno</label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required>
                    </div>

                    {{-- Apellido Materno --}}
                    <div class="form-field">
                        <label for="apellido_materno">Apellido Materno</label>
                        <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" required>
                    </div>
                </div>

                <div class="form-group-double">
                    {{-- Email --}}
                    <div class="form-field">
                        <label for="email">Correo Electrónico (Email)</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    {{-- Teléfono --}}
                    <div class="form-field">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}" required>
                    </div>
                </div>

                <div class="form-group-triple">
                    {{-- RFC --}}
                    <div class="form-field">
                        <label for="RFC">RFC</label>
                        <input type="text" id="RFC" name="RFC" value="{{ old('RFC') }}">
                    </div>

                    {{-- Fecha de Nacimiento --}}
                    <div class="form-field">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
                    </div>

                    {{-- Edad --}}
                    <div class="form-field">
                        <label for="edad">Edad</label>
                        <input type="number" id="edad" name="edad" value="{{ old('edad') }}">
                    </div>
                </div>
                
                {{-- Sección: Dirección --}}
                <h3>📍 Dirección</h3>
                <hr>

                {{-- Dirección (se asume que hay 6 campos, agrupados en dos filas de 3) --}}
                <div class="form-group-triple">
                    <div class="form-field">
                        <label for="calle_1">Calle</label>
                        <input type="text" id="calle" name="calle" value="" required>
                    </div>
                    <div class="form-field">
                        <label for="colonia">Colonia</label>
                        <input type="text" id="colonia" name="colonia" value="" required>
                    </div>
                    <div class="form-field">
                        <label for="ciudad">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" value="" required>
                    </div>
                </div>
                
                <div class="form-group-triple">
                    
                    <div class="form-field">
                        <label for="estado">Estado</label>
                        <input type="text" id="estado" name="estado" value="" required>
                    </div>
                    <div class="form-field">
                        <label for="codigo_postal">Código Postal</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" value="" required>
                    </div>
                </div>

                {{-- Sección: Contraseña y Carrera --}}
                <h3>🔒 Acceso e Inscripción</h3>
                <hr>

                <div class="form-group-triple">
                    {{-- Contraseña --}}
                    <div class="form-field">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    {{-- Confirmar Contraseña --}}
                    <div class="form-field">
                        <label for="password_confirmation">Confirmar Contraseña</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    {{-- Carrera --}}
                    <div class="form-field">
                        <label for="carrera">Carrera</label>
                        <select id="carrera" name="carrera" required>
                            <option value="">Seleccione una Carrera</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id }}"{{ old('carrera') == $carrera->id ? 'selected' : '' }}>{{ $carrera->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Confirmar Contraseña --}}
                    <div class="form-field">
                        <label for="departamento">Departamento</label>
                        <input type="text" id="departamento" name="departamento" required>
                    </div>
                </div>

                <div class="form-action-buttons">
                    <button type="submit" class="submit-button">Registrar Docente</button>
                </div>
            </form>
        </div>
    </div>
@endsection