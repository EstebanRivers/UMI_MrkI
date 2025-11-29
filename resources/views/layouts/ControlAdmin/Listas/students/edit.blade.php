@extends('layouts.app')

@section('title', 'Edición de Alumno - ' . session('active_institution_name'))

@vite(['resources/css/ControlEsc/base.css','resources/js/app.js'])

@section('content')
    <div class="form-container">
        <div class="header-section">
            <h2 class="form-title">✏️ Edición de Alumno / Reinscripción</h2>
            {{-- Botón de regreso opcional --}}
            <a href="{{ request()->routeIs('control.*') ? route('control.students.index') : route('escolar.students.index') }}" 
               class="btn-back" style="float:right; text-decoration:none; color: #666; font-size: 0.9rem;">
                <i class="fa-solid fa-arrow-left"></i> Volver a Lista
            </a>
        </div>
        
        <div class="form-body">
            {{-- ¡IMPORTANTE!: enctype="multipart/form-data" es OBLIGATORIO para subir archivos --}}
            <form method="POST" 
                  action="{{ request()->routeIs('control.*') ? route('control.students.update', $user->id) : route('escolar.students.update', $user->id) }}" 
                  class="registration-form" 
                  enctype="multipart/form-data">
                
                @csrf
                @method('PUT')

                {{-- Errores --}}
                @if ($errors->any())
                    <div class="message-error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                {{-- 0. STATUS (SOLO LECTURA - REGLA DE NEGOCIO) --}}
                <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #ffc107;">
                    <label style="color: #856404; font-weight: bold;">Estado Actual:</label>
                    <span style="background: #ffc107; color: #000; padding: 3px 10px; border-radius: 15px; font-weight: bold; font-size: 0.9rem;">
                        {{ $user->academicProfile->status ?? 'Sin Status' }}
                    </span>
                    
                    {{-- Mostrar Matrícula si existe --}}
                    @if(!empty($user->academicProfile->matricula))
                        <span style="margin-left: 15px; font-weight: bold; color: #2c3e50;">
                            <i class="fa-solid fa-id-card"></i> Matrícula: {{ $user->academicProfile->matricula }}
                        </span>
                    @else
                        <span style="margin-left: 15px; color: #e74c3c; font-size: 0.85rem;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Sin Matrícula Asignada
                        </span>
                    @endif

                    <p style="margin: 5px 0 0; font-size: 0.85rem; color: #666;">
                        <i class="fa-solid fa-lock"></i> El estatus se actualiza automáticamente al validar documentos o pagos.
                    </p>
                </div>

                {{-- ==================================================================================== --}}
                {{-- 1. SECCIÓN DE CONTRASEÑA                         --}}
                {{-- ==================================================================================== --}}
                @if(!empty($user->academicProfile->matricula))
                    <div style="background-color: #d4edda; padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #28a745;">
                        <h4 style="margin-top:0; color: #155724; margin-bottom: 10px;">
                            <i class="fa-solid fa-key"></i> Asignación de Acceso (Contraseña)
                        </h4>
                        <div class="form-group-double">
                            <div class="form-field">
                                <label style="color: #155724;">Nueva Contraseña</label>
                                <input type="password" name="password" placeholder="Mínimo 8 caracteres" style="border: 1px solid #28a745;">
                            </div>
                            <div class="form-field">
                                <label style="color: #155724;">Confirmar Contraseña</label>
                                <input type="password" name="password_confirmation" placeholder="Repetir contraseña" style="border: 1px solid #28a745;">
                            </div>
                        </div>
                        <small style="color: #155724;">Dejar en blanco si no se desea cambiar la contraseña actual.</small>
                    </div>
                @else
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px dashed #ccc; text-align: center; color: #666;">
                        <i class="fa-solid fa-lock"></i> <strong>Acceso Bloqueado:</strong> La asignación de contraseña estará disponible cuando el alumno complete su pago y tenga matrícula.
                    </div>
                @endif
                {{-- ==================================================================================== --}}


                {{-- 2. TIPO DE ASPIRANTE (LÓGICA ANFITRIÓN) --}}
                <h3>📋 Información Laboral (Anfitrión)</h3>
                <hr>
                <div class="form-group-double" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <div class="form-field" style="flex-direction: row; align-items: center; gap: 10px;">
                        <input type="checkbox" id="is_anfitrion" name="is_anfitrion" value="1" 
                            {{ old('is_anfitrion', $user->academicProfile->is_anfitrion ?? false) ? 'checked' : '' }}
                            style="width: 20px; height: 20px;">
                        <label for="is_anfitrion" style="margin:0; font-weight: bold;">¿Es Anfitrión (Trabajador)?</label>
                    </div>
                </div>

                {{-- SECCIÓN OCULTA DINÁMICA --}}
                <div id="seccion-laboral" style="display: none; background-color: #e8f6f3; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #27ae60;">
                    <div class="form-group-double">
                        <div class="form-field">
                            <label>Departamento</label>
                            <select name="department_id" id="department_id">
                                <option value="">Seleccione...</option>
                                @foreach($departamentos ?? [] as $dep)
                                    <option value="{{ $dep->id }}" {{ old('department_id', $user->department_id) == $dep->id ? 'selected' : '' }}>
                                        {{ $dep->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Puesto</label>
                            <select name="workstation_id" id="workstation_id">
                                <option value="">Seleccione...</option>
                                @foreach($puestos ?? [] as $pto)
                                    <option value="{{ $pto->id }}" {{ old('workstation_id', $user->workstation_id) == $pto->id ? 'selected' : '' }}>
                                        {{ $pto->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 3. DATOS PERSONALES --}}
                <h3>👤 Datos Personales</h3>
                <hr>
                <div class="form-group-triple">
                    <div class="form-field">
                        <label>Nombre(s)</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $user->nombre) }}" required>
                    </div>
                    <div class="form-field">
                        <label>Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $user->apellido_paterno) }}" required>
                    </div>
                    <div class="form-field">
                        <label>Apellido Materno</label>
                        <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $user->apellido_materno) }}" required>
                    </div>
                </div>

                <div class="form-group-triple">
                    <div class="form-field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="form-field">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $user->telefono) }}" required>
                    </div>
                    <div class="form-field">
                        <label>RFC</label>
                        <input type="text" name="RFC" value="{{ old('RFC', $user->RFC) }}">
                    </div>
                </div>

                <div class="form-group-double">
                    <div class="form-field">
                        <label>Fecha Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $user->fecha_nacimiento) }}" required>
                    </div>
                    <div class="form-field">
                        <label>Edad (Auto)</label>
                        <input type="number" id="edad" name="edad" value="{{ old('edad', $user->edad) }}" readonly style="background-color: #eee;">
                    </div>
                </div>

                {{-- 4. DIRECCIÓN --}}
                <h3>📍 Dirección</h3>
                <hr>
                <div class="form-group-triple">
                    <div class="form-field">
                        <label>Calle</label>
                        <input type="text" name="calle" value="{{ old('calle', $user->address->calle ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Colonia</label>
                        <input type="text" name="colonia" value="{{ old('colonia', $user->address->colonia ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>CP</label>
                        <input type="text" name="codigo_postal" value="{{ old('codigo_postal', $user->address->codigo_postal ?? '') }}" required>
                    </div>
                </div>
                <div class="form-group-double">
                    <div class="form-field">
                        <label>Ciudad</label>
                        <input type="text" name="ciudad" value="{{ old('ciudad', $user->address->ciudad ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Estado</label>
                        <input type="text" name="estado" value="{{ old('estado', $user->address->estado ?? '') }}" required>
                    </div>
                </div>

                {{-- 5. ACADÉMICO Y DOCUMENTOS --}}
                <h3>🎓 Académico y Documentación</h3>
                <hr>

                <div class="form-group-double">
                    <div class="form-field">
                        <label>Carrera</label>
                        <select name="carrera" required>
                            <option value="">Seleccione...</option>
                            {{-- VERIFICAMOS SI EXISTE LA VARIABLE $CARRERAS --}}
                            @if(isset($carreras))
                                @foreach ($carreras as $carrera)
                                    <option value="{{ $carrera->id }}" 
                                        {{ (old('carrera', $user->academicProfile->career_id ?? '') == $carrera->id) ? 'selected' : '' }}>
                                        {{ $carrera->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Semestre</label>
                        <input type="number" name="semestre" value="{{ old('semestre', $user->academicProfile->semestre ?? 1) }}">
                    </div>
                </div>

                {{-- VISUALIZACIÓN DE DOCUMENTOS --}}
                <div class="docs-container" style="margin-top: 15px;">
                    <h4 style="color: #666; font-size: 0.9rem; text-transform: uppercase;">Documentos Cargados</h4>
                    
                    <div class="form-group-double">
                        {{-- Acta --}}
                        <div class="form-field">
                            <label>Acta Nacimiento</label>
                            @if($user->academicProfile->doc_acta_nacimiento)
                                <a href="{{ Storage::url($user->academicProfile->doc_acta_nacimiento) }}" target="_blank" class="btn-ver-doc">
                                    <i class="fa-regular fa-file-pdf"></i> Ver Documento Actual
                                </a>
                            @else
                                <span style="color:red; font-size:0.8rem;">No cargado</span>
                            @endif
                            <input type="file" name="doc_acta_nacimiento" accept=".pdf,.jpg,.png">
                            <small>Subir solo si desea reemplazar</small>
                        </div>

                        {{-- Certificado --}}
                        <div class="form-field">
                            <label>Certificado Prepa</label>
                            @if($user->academicProfile->doc_certificado_prepa)
                                <a href="{{ Storage::url($user->academicProfile->doc_certificado_prepa) }}" target="_blank" class="btn-ver-doc">
                                    <i class="fa-regular fa-file-pdf"></i> Ver Documento Actual
                                </a>
                            @else
                                <span style="color:red; font-size:0.8rem;">No cargado</span>
                            @endif
                            <input type="file" name="doc_certificado_prepa" accept=".pdf,.jpg,.png">
                        </div>
                    </div>

                    <div class="form-group-double">
                        {{-- CURP --}}
                        <div class="form-field">
                            <label>CURP</label>
                            @if($user->academicProfile->doc_curp)
                                <a href="{{ Storage::url($user->academicProfile->doc_curp) }}" target="_blank" class="btn-ver-doc">
                                    <i class="fa-regular fa-file-pdf"></i> Ver Documento Actual
                                </a>
                            @else
                                <span style="color:red; font-size:0.8rem;">No cargado</span>
                            @endif
                            <input type="file" name="doc_curp" accept=".pdf,.jpg,.png">
                        </div>

                        {{-- INE --}}
                        <div class="form-field">
                            <label>INE (Opcional)</label>
                            @if($user->academicProfile->doc_ine)
                                <a href="{{ Storage::url($user->academicProfile->doc_ine) }}" target="_blank" class="btn-ver-doc">
                                    <i class="fa-regular fa-file-pdf"></i> Ver Documento Actual
                                </a>
                            @else
                                <span style="color:gray; font-size:0.8rem;">No cargado</span>
                            @endif
                            <input type="file" name="doc_ine" accept=".pdf,.jpg,.png">
                        </div>
                    </div>
                </div>

                <div class="form-action-buttons">
                    <button type="submit" class="submit-button">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ESTILOS EXTRA PARA LOS LINKS --}}
    <style>
        .btn-ver-doc {
            display: inline-block;
            margin-bottom: 5px;
            color: #2980b9;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-ver-doc:hover { text-decoration: underline; color: #1abc9c; }
        .submit-button { background: #27ae60; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-size: 1rem; font-weight: 600; }
        .submit-button:hover { background: #219150; }
        .form-group-double { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group-triple { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-field { display: flex; flex-direction: column; }
        .form-field label { font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; color: #34495e; }
        .form-field input, .form-field select { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
    </style>

    {{-- SCRIPTS DE LÓGICA --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. ANFITRIÓN TOGGLE
            const checkAnfitrion = document.getElementById('is_anfitrion');
            const seccionLaboral = document.getElementById('seccion-laboral');
            const inputDepto = document.getElementById('department_id');
            const inputPuesto = document.getElementById('workstation_id');

            function toggleLaboral() {
                if (checkAnfitrion && seccionLaboral) {
                    if (checkAnfitrion.checked) {
                        seccionLaboral.style.display = 'block';
                    } else {
                        seccionLaboral.style.display = 'none';
                        if(inputDepto) inputDepto.value = "";
                        if(inputPuesto) inputPuesto.value = "";
                    }
                }
            }

            if (checkAnfitrion) {
                checkAnfitrion.addEventListener('change', toggleLaboral);
                toggleLaboral(); // Ejecutar al cargar
            }

            // 2. CÁLCULO DE EDAD
            const inputFecha = document.getElementById('fecha_nacimiento');
            const inputEdad = document.getElementById('edad');

            function calcularEdad() {
                if (!inputFecha || !inputFecha.value) return;
                const fecha = new Date(inputFecha.value);
                const hoy = new Date();
                let edad = hoy.getFullYear() - fecha.getFullYear();
                const m = hoy.getMonth() - fecha.getMonth();
                if (m < 0 || (m === 0 && hoy.getDate() < (fecha.getDate() + 1))) { // +1 por ajuste de zona horaria simple
                    edad--;
                }
                inputEdad.value = edad >= 0 ? edad : 0;
            }

            if (inputFecha) {
                inputFecha.addEventListener('change', calcularEdad);
            }
        });
    </script>
@endsection