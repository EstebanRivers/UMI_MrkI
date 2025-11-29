@extends('layouts.app')

@section('title', isset($alumno) ? 'Proceso de Reinscripción' : 'Registro de Aspirante')

@vite(['resources/css/ControlEsc/base.css','resources/js/app.js'])

@section('content')

    {{-- 1. BLOQUE DE ERROR (CANDADO) --}}
    @if(session('error'))
<div class="umi-error-card">
            <h1><i class="fa-solid fa-lock"></i></h1>
            
            <h3>
                {{-- Título dinámico según el tipo de error --}}
                {{ Str::contains(session('error'), 'Periodo') ? 'Acción Bloqueada' : 'Error del Sistema' }}
            </h3>
            
            <p style="font-size: 1.1rem;">{{ session('error') }}</p>
            
            {{-- Solo mostramos el botón si es error de Periodo --}}
            @if(Str::contains(session('error'), 'Periodo'))
                <a href="{{ route('ajustes.show', ['seccion' => 'periods']) }}" class="umi-btn-error">
                    <i class="fa-solid fa-gear"></i> Ir a Ajustes para Activar Periodo
                </a>
            @endif
        </div> 
    {{-- 2. SI NO HAY ERROR, MOSTRAMOS EL FORMULARIO --}}
    @else
        <div class="form-container">
    <div class="form-container">
        <div class="header-section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="form-title">
                    {{-- Título Dinámico --}}
                    @if(isset($alumno))
                        🔄 Reinscripción de Alumno <span style="font-size: 0.8em; opacity: 0.8;">(Al Semestre {{ $alumno->semestre + 1 }})</span>
                    @else
                        📝 Nuevo Registro de Aspirante
                    @endif
                </h2>
                <a href="{{ route('escolar.students.index') }}" class="btn-back" style="text-decoration: none; color: #666;">
                    <i class="fa-solid fa-arrow-left"></i> Volver a Lista
                </a>
            </div>

            {{-- Badge de Status para Reinscripciones --}}
            @if(isset($alumno))
                <div class="status-bar" style="margin-top: 10px; background: #fff3cd; padding: 8px 15px; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <strong>Status Actual:</strong> 
                    <span class="badge-status {{ strtolower($alumno->status) }}">{{ $alumno->status ?? 'Inactivo' }}</span>
                    <span style="font-size: 0.85rem; margin-left: 10px; color: #666;">
                        <i class="fa-solid fa-info-circle"></i> Al completar este proceso y validar el pago, el alumno pasará a <strong>Activo</strong>.
                    </span>
                </div>
            @endif
        </div>
        
        <div class="form-body">
            {{-- Formulario Único: Maneja tanto STORE (Nuevo) como UPDATE (Reinscripción) --}}
            <form method="POST" 
                  action="{{ isset($alumno) ? route('escolar.inscripcion.update', $alumno->id) : route('escolar.inscripcion.store') }}" 
                  class="registration-form" 
                  enctype="multipart/form-data">
                
                @csrf
                @if(isset($alumno))
                    @method('PUT')
                @endif
                
                {{-- Mensajes de Feedback --}}
                @if (session('success')) <div class="message-success">{{ session('success') }}</div> @endif
                @if (session('error')) <div class="message-error">{{ session('error') }}</div> @endif
                @if ($errors->any())
                    <div class="message-error">
                        <ul>@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                    </div>
                @endif

                {{-- 1. TIPO DE REGISTRO --}}
                <h3>📋 Clasificación del Ingreso</h3>
                <hr>
                <div class="form-group-double" style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e9ecef;">
                    <div class="form-field" style="flex-direction: row; align-items: center; gap: 10px;">
                        <input type="checkbox" id="is_anfitrion" name="is_anfitrion" value="1" 
                               style="width: 20px; height: 20px; cursor: pointer;"
                               {{ old('is_anfitrion', $alumno->is_anfitrion ?? false) ? 'checked' : '' }}>
                        <label for="is_anfitrion" style="margin: 0; cursor: pointer; font-weight: 600; color: #2c3e50;">
                            ¿Es Anfitrión? (Colaborador de Mundo Imperial)
                        </label>
                    </div>
                </div>

                {{-- SECCIÓN LABORAL (Dinámica) --}}
                <div id="seccion-laboral" style="display: none; background-color: #e8f6f3; padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #27ae60; margin-top: 15px;">
                    <h4 style="color: #27ae60; margin-top: 0; margin-bottom: 15px;"><i class="fa-solid fa-briefcase"></i> Datos Laborales</h4>
                    <div class="form-group-double">
                        <div class="form-field">
                            <label>Departamento</label>
                            <select name="department_id" id="department_id">
                                <option value="">Seleccione Departamento...</option>
                                @foreach($departamentos as $dep)
                                    <option value="{{ $dep->id }}" {{ old('department_id', $alumno->department_id ?? '') == $dep->id ? 'selected' : '' }}>
                                        {{ $dep->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Puesto</label>
                            <select name="workstation_id" id="workstation_id">
                                <option value="">Seleccione Puesto...</option>
                                @foreach($puestos as $pto)
                                    <option value="{{ $pto->id }}" {{ old('workstation_id', $alumno->workstation_id ?? '') == $pto->id ? 'selected' : '' }}>
                                        {{ $pto->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 2. DATOS PERSONALES --}}
                <h3>👤 Datos Personales</h3>
                <hr>
                <div class="form-group-triple">
                    <div class="form-field">
                        <label>Nombre(s)</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $alumno->nombre ?? '') }}" required placeholder="Ej. Juan Pablo">
                    </div>
                    <div class="form-field">
                        <label>Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $alumno->apellido_paterno ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Apellido Materno</label>
                        <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $alumno->apellido_materno ?? '') }}" required>
                    </div>
                </div>

                <div class="form-group-triple">
                    <div class="form-field">
                        <label>Email Personal</label>
                        <input type="email" name="email" value="{{ old('email', $alumno->email ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Teléfono Celular</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $alumno->telefono ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>RFC <small>(Opcional)</small></label>
                        <input type="text" name="RFC" value="{{ old('RFC', $alumno->RFC ?? '') }}" placeholder="Generación automática si vacío">
                    </div>
                </div>

                <div class="form-group-double">
                    <div class="form-field">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                               value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Edad Calculada</label>
                        <input type="number" id="edad" name="edad" value="{{ old('edad', $alumno->edad ?? '') }}" readonly style="background-color: #eee; cursor: not-allowed;">
                    </div>
                </div>

                {{-- 3. DIRECCIÓN --}}
                <h3>📍 Domicilio</h3>
                <hr>
                <div class="form-group-triple">
                    <div class="form-field">
                        <label>Calle y Número</label>
                        <input type="text" name="calle" value="{{ old('calle', $alumno->calle ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Colonia / Asentamiento</label>
                        <input type="text" name="colonia" value="{{ old('colonia', $alumno->colonia ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Código Postal</label>
                        <input type="text" name="codigo_postal" value="{{ old('codigo_postal', $alumno->codigo_postal ?? '') }}" required>
                    </div>
                </div>
                <div class="form-group-double">
                    <div class="form-field">
                        <label>Ciudad / Municipio</label>
                        <input type="text" name="ciudad" value="{{ old('ciudad', $alumno->ciudad ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Estado</label>
                        <input type="text" name="estado" value="{{ old('estado', $alumno->estado ?? '') }}" required>
                    </div>
                </div>

                {{-- 4. ACADÉMICO Y DOCUMENTOS --}}
                <h3>🎓 Datos Académicos y Documentación</h3>
                <hr>
                <div class="form-group-double">
                    <div class="form-field">
                        <label>Carrera a Cursar</label>
                        <select name="carrera_id" required>
                            <option value="">Seleccione una carrera...</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id }}" {{ old('carrera_id', $alumno->carrera_id ?? '') == $carrera->id ? 'selected' : '' }}>
                                    {{ $carrera->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Semestre a Inscribir</label>
                        {{-- Lógica Automática: Si es nuevo es 1, si existe es Semestre Actual + 1 --}}
                        <input type="number" name="semestre" 
                               value="{{ old('semestre', isset($alumno) ? ($alumno->semestre + 1) : 1) }}" 
                               readonly style="background-color: #e9ecef; font-weight: bold; border-color: #ced4da;">
                        @if(isset($alumno))
                            <small style="color: #666;">(Avanza del semestre {{ $alumno->semestre }} al {{ $alumno->semestre + 1 }})</small>
                        @endif
                    </div>
                </div>

                {{-- CARGA DE DOCUMENTOS --}}
                <div class="docs-container" style="background: #ffffff; padding: 20px; border: 1px dashed #3498db; border-radius: 8px; margin-top: 20px;">
                    <h4 style="margin-top:0; color: #2980b9;"><i class="fa-solid fa-cloud-arrow-up"></i> Documentación Requerida</h4>
                    
                    <div class="form-group-double">
                        <div class="form-field">
                            <label>Acta de Nacimiento (PDF)</label>
                            <input type="file" name="doc_acta_nacimiento" accept=".pdf">
                            @if(isset($alumno) && $alumno->doc_acta_nacimiento)
                                <a href="{{ asset('storage/'.$alumno->doc_acta_nacimiento) }}" target="_blank" class="link-view-doc">
                                    <i class="fa-regular fa-eye"></i> Ver Documento Actual
                                </a>
                            @endif
                        </div>
                        <div class="form-field">
                            <label>Certificado de Preparatoria (PDF)</label>
                            <input type="file" name="doc_certificado_prepa" accept=".pdf">
                            @if(isset($alumno) && $alumno->doc_certificado_prepa)
                                <a href="{{ asset('storage/'.$alumno->doc_certificado_prepa) }}" target="_blank" class="link-view-doc">
                                    <i class="fa-regular fa-eye"></i> Ver Documento Actual
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="form-group-double">
                        <div class="form-field">
                            <label>CURP (PDF)</label>
                            <input type="file" name="doc_curp" accept=".pdf">
                            @if(isset($alumno) && $alumno->doc_curp)
                                <a href="{{ asset('storage/'.$alumno->doc_curp) }}" target="_blank" class="link-view-doc">
                                    <i class="fa-regular fa-eye"></i> Ver Documento Actual
                                </a>
                            @endif
                        </div>
                        <div class="form-field">
                            <label>INE (Opcional)</label>
                            <input type="file" name="doc_ine" accept=".pdf,.jpg,.png">
                            @if(isset($alumno) && $alumno->doc_ine)
                                <a href="{{ asset('storage/'.$alumno->doc_ine) }}" target="_blank" class="link-view-doc">
                                    <i class="fa-regular fa-eye"></i> Ver Documento Actual
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN HISTORIAL (Solo para Reinscripciones) --}}
                @if(isset($alumno) && isset($historialInscripciones))
                    <div class="history-container" style="margin-top: 30px;">
                        <h3>📂 Historial de Inscripciones Anteriores</h3>
                        <hr>
                        <div style="overflow-x: auto;">
                            <table class="umi-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                                <thead style="background: #f1f1f1;">
                                    <tr>
                                        <th style="padding: 10px; text-align: left;">Semestre</th>
                                        <th style="padding: 10px; text-align: left;">Fecha</th>
                                        <th style="padding: 10px; text-align: left;">Carrera</th>
                                        <th style="padding: 10px; text-align: center;">Documentos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($historialInscripciones as $hist)
                                        <tr style="border-bottom: 1px solid #eee;">
                                            <td style="padding: 10px;">Semestre {{ $hist->semestre }}</td>
                                            <td style="padding: 10px;">{{ \Carbon\Carbon::parse($hist->created_at)->format('d/m/Y') }}</td>
                                            <td style="padding: 10px;">{{ $hist->carrera->name ?? 'N/A' }}</td>
                                            <td style="padding: 10px; text-align: center;">
                                                <a href="#" style="color: #3498db; text-decoration: none;">
                                                    <i class="fa-solid fa-folder-open"></i> Ver Expediente
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="padding: 15px; text-align: center; color: #999;">
                                                No hay registros anteriores disponibles.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="form-action-buttons">
                    <button type="submit" class="submit-button">
                        <i class="fa-solid fa-save"></i> {{ isset($alumno) ? 'Guardar Reinscripción' : 'Registrar Aspirante' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPTS LÓGICOS --}}
{{-- SCRIPTS LÓGICOS --}}
    <script>
        // 1. Definimos la función con la lógica (Sin envolverla en eventos todavía)
        function ejecutarLogicaInscripcion() {
            
            // --- LÓGICA TRIBILÍN (ANFITRION TOGGLE) ---
            const checkAnfitrion = document.getElementById('is_anfitrion');
            const seccionLaboral = document.getElementById('seccion-laboral');
            const inputDepto = document.getElementById('department_id');
            const inputPuesto = document.getElementById('workstation_id');

            if (checkAnfitrion && seccionLaboral) {
                function toggleAnfitrion() {
                    if (checkAnfitrion.checked) {
                        seccionLaboral.style.display = 'block';
                        seccionLaboral.style.opacity = 0;
                        setTimeout(() => seccionLaboral.style.opacity = 1, 50);
                    } else {
                        seccionLaboral.style.display = 'none';
                        if(inputDepto) inputDepto.value = "";
                        if(inputPuesto) inputPuesto.value = "";
                    }
                }

                // Evento change
                checkAnfitrion.addEventListener('change', toggleAnfitrion);
                // Estado inicial al cargar
                seccionLaboral.style.transition = 'opacity 0.3s ease';
                toggleAnfitrion(); 
            }

            // --- CÁLCULO AUTOMÁTICO DE EDAD ---
            const inputFecha = document.getElementById('fecha_nacimiento');
            const inputEdad = document.getElementById('edad');

            if (inputFecha && inputEdad) {
                function calcularEdad() {
                    const fechaTexto = inputFecha.value;
                    if (!fechaTexto) return;

                    const partes = fechaTexto.split('-');
                    const fechaNacimiento = new Date(partes[0], partes[1] - 1, partes[2]);
                    const hoy = new Date();
                    
                    let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
                    const mes = hoy.getMonth() - fechaNacimiento.getMonth();
                    
                    if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                        edad--;
                    }
                    inputEdad.value = edad >= 0 ? edad : 0;
                }

                inputFecha.addEventListener('change', calcularEdad);
                // Calcular si ya hay fecha (edición)
                if(inputFecha.value) calcularEdad();
            }
        }

        // 2. EJECUCIÓN (La parte importante para que funcione siempre)
        
        // Ejecutar inmediatamente (Para cuando carga por AJAX/SPA)
        ejecutarLogicaInscripcion();

        // Y también asegurar ejecución si es una recarga completa (F5)
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", ejecutarLogicaInscripcion);
        }
    </script>

   
@endif
@endsection