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
                  id="inscriptionForm"
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

                    {{-- SELECTOR DE USUARIOS EXISTENTES (Solo visible si es anfitrión) --}}
                    <div id="container-buscador-usuarios" class="form-field" style="display: none;">
                        <label style="color: #2980b9; font-weight: bold;"><i class="fa-solid fa-magnifying-glass"></i> Buscar Anfitrión Existente</label>
                        <select id="user_selector" class="select2" style="width: 100%; padding: 8px;">
                            <option value="">-- Seleccionar para Autocompletar --</option>
                            @if(isset($usuariosAnfitriones))
                                @foreach($usuariosAnfitriones as $u)
                                    <option value="{{ $u->id }}" 
                                        data-nombre="{{ $u->nombre }}" 
                                        data-apellido_p="{{ $u->apellido_paterno }}" 
                                        data-apellido_m="{{ $u->apellido_materno }}"
                                        data-email="{{ $u->email }}"
                                        data-telefono="{{ $u->telefono }}"
                                        data-workstation="{{ $u->workstation_id }}"
                                        data-department="{{ $u->department_id }}"
                                        data-rfc="{{ $u->RFC }}"> {{ $u->nombre }} {{ $u->apellido_paterno }} - ({{ $u->email }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <small style="color: #666;">Selecciona un usuario para cargar sus datos automáticamente y vincularlo.</small>
                        
                        {{-- INPUT OCULTO: Aquí guardaremos el ID si seleccionan a alguien --}}
                        <input type="hidden" name="existing_user_id" id="existing_user_id" value="">
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
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $alumno->nombre ?? '') }}" required placeholder="Ej. Juan Pablo">
                    </div>
                    <div class="form-field">
                        <label>Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno', $alumno->apellido_paterno ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Apellido Materno</label>
                        <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno', $alumno->apellido_materno ?? '') }}" required>
                    </div>
                </div>

                <div class="form-group-triple">
                    <div class="form-field">
                        <label>Email Personal</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $alumno->email ?? '') }}" required>
                        <small id="email_helper" style="display:none; color: #2980b9;">Este email está vinculado a la cuenta existente.</small>
                    </div>
                    <div class="form-field">
                        <label>Teléfono Celular</label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $alumno->telefono ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>RFC <small>(Opcional)</small></label>
                        <input type="text" name="RFC" id="inputRFC" 
                            value="{{ old('RFC', $alumno->RFC ?? '') }}" 
                            placeholder="Generación automática si vacío"
                            {{-- Agrega 'readonly' si ya existe un RFC para el alumno actual --}}
                            {{ isset($alumno) && !empty($alumno->RFC) ? 'readonly' : '' }}
                            style="{{ isset($alumno) && !empty($alumno->RFC) ? 'background-color: #f0f0f0;' : '' }}">
                    </div>
                </div>

                <div class="form-group-double">
                    <div class="form-field">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                               value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento ?? '') }}" required>
                    </div>
                    <div class="form-field">
                        <label>Edad</label>
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

                {{-- 6. FACTURACIÓN DINÁMICA --}}
                <div class="billing-container" style="background: #fdf2f2; padding: 20px; border: 1px solid #e74c3c; border-radius: 8px; margin-top: 20px;">
                    <h4 style="margin-top:0; color: #c0392b;"><i class="fa-solid fa-money-bill-wave"></i> Ficha de Pago / Facturación</h4>
                    <hr style="border-top: 1px solid #e74c3c; opacity: 0.3;">
                    
                    <div style="display: flex; gap: 15px; align-items: flex-start;">
                        <div style="flex: 0 0 auto; margin-top: 5px;">
                            <input type="checkbox" name="generar_factura" id="generar_factura" value="1" style="width: 20px; height: 20px; cursor: pointer;">
                        </div>
                        <div style="width: 100%;">
                            <label for="generar_factura" style="font-weight: bold; cursor: pointer; color: #c0392b; font-size: 1.05em;">
                                Generar Ficha de Pago para este Movimiento
                            </label>
                            <p style="font-size: 0.9em; color: #666; margin: 5px 0;">
                                Marque esta opción para crear una cuenta por cobrar. Los Anfitriones generalmente <u>no requieren</u> este cargo.
                            </p>
                            
                            {{-- DETALLES DINÁMICOS DE FACTURACIÓN --}}
                            <div id="billing-details" style="display: none; margin-top: 15px; background: white; padding: 15px; border-radius: 6px; border: 1px dashed #e74c3c;">
                                
                                {{-- 1. Período Activo --}}
                                <label for="modal_period_id" style="font-weight:bold; display:block; margin-top:10px;">Período Activo:</label>
                                <select id="modal_period_id" name="period_id" class="filter-select" style="width:100%; background-color: #e9ecef; pointer-events: none;" readonly tabindex="-1">
                                    @if(isset($periods))
                                        @foreach ($periods as $period)
                                            @if($period->is_active == 1)
                                                <option value="{{ $period->id }}" selected>{{ $period->name }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>

                                {{-- 2. Concepto --}}
                                <label for="modal_concepto" style="font-weight:bold; display:block; margin-top:10px;">Concepto:</label>
                                <select id="modal_concepto" name="concepto" class="filter-select" style="width: 100%; padding: 8px;">
                                    <option value="" data-amount="">-- Seleccione un concepto --</option>
                                    @if(isset($conceptosDisponibles))
                                        @foreach($conceptosDisponibles as $c)
                                            <option value="{{ $c->concept }}" data-amount="{{ $c->amount }}">
                                                {{ $c->concept }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>

                                {{-- 3. Monto --}}
                                <label for="modal_monto_visible" style="font-weight:bold; display:block; margin-top:10px;">Monto:</label>
                                <input type="text" 
                                       id="modal_monto_visible" 
                                       readonly 
                                       placeholder="$ 0.00"
                                       style="width: 100%; padding: 10px; background-color: #f8f9fa; border: 1px solid #ccc; border-radius: 4px; font-weight: bold; color: #333; transition: background-color 0.3s;">
                                <input type="hidden" id="modal_monto" name="monto">

                                {{-- 4. Fecha Vencimiento --}}
                                <strong style="display:block; margin-top: 15px;">Fecha Vencimiento (Asignada por sistema):</strong>
                                <p id="texto_fecha_vencimiento" style="font-weight: bold; color: #223F70; margin: 5px 0 15px 0; font-size: 1.1em;">
                                    {{ \Carbon\Carbon::now()->addDays(7)->format('d/m/Y') }}
                                </p>

                                {{-- 5. Estado --}}
                                <label for="modal_status" style="font-weight:bold; display:block; margin-top:10px;">Estado:</label>
                                <select id="modal_status" name="status" style="width: 100%; padding: 8px; margin-bottom: 20px;">
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Pagada">Pagada</option>
                                </select>

                                {{-- 6. Archivos (OPCIONALES) --}}
                                <label for="modal_archivo_pdf" style="font-weight:bold; display:block; margin-top:10px;">Archivo (PDF) (Opcional):</label>
                                <input type="file" id="modal_archivo_pdf" name="archivo" accept=".pdf" style="width: 100%;">
                                <small style="color: #666;">Solo archivos .pdf</small>

                                <label for="modal_archivo_xml" style="font-weight:bold; display:block; margin-top:10px;">Subir XML (Opcional):</label>
                                <input type="file" id="modal_archivo_xml" name="archivo_xml" accept=".xml,text/xml" style="width: 100%;">
                                <small style="color: #666;">Solo archivos .xml</small>
                            </div>
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

<script>
    function ejecutarLogicaInscripcion() {
        // ELEMENTOS GENERALES
        const checkAnfitrion = document.getElementById('is_anfitrion');
        const seccionLaboral = document.getElementById('seccion-laboral');
        const containerBuscador = document.getElementById('container-buscador-usuarios');
        const selectorUsuario = document.getElementById('user_selector');
        const hiddenUserId = document.getElementById('existing_user_id');
        const emailHelper = document.getElementById('email_helper');
        
        // ELEMENTOS PERSONALES Y LABORALES
        const inputNombre = document.getElementById('nombre');
        const inputPat = document.getElementById('apellido_paterno');
        const inputMat = document.getElementById('apellido_materno');
        const inputEmail = document.getElementById('email');
        const inputTel = document.getElementById('telefono');
        const inputRFC = document.getElementById('inputRFC');
        const inputDepto = document.getElementById('department_id');
        const inputPuesto = document.getElementById('workstation_id');
        const inputFechaNac = document.getElementById('fecha_nacimiento');
        const inputEdad = document.getElementById('edad');

        // ELEMENTOS FACTURACIÓN
        const checkFactura = document.getElementById('generar_factura');
        const billingDetails = document.getElementById('billing-details');
        const conceptoSelect = document.getElementById('modal_concepto');
        const montoVisible = document.getElementById('modal_monto_visible');
        const montoHidden = document.getElementById('modal_monto');


        // --- FUNCIONES AUXILIARES ---

        // 1. CONTROL DE SOLO LECTURA/BLOQUEO
        const setReadOnly = (inputElement, isReadOnly) => {
            if (inputElement) {
                if (inputElement.tagName === 'SELECT') {
                    inputElement.disabled = isReadOnly;
                } else {
                    inputElement.readOnly = isReadOnly;
                }
                inputElement.style.backgroundColor = isReadOnly ? "#e9ecef" : "";
            }
        };

        // 2. LIMPIEZA DE CAMPOS PERSONALES Y LABORALES
        function limpiarCamposPersonales() {
            // Aplicar desbloqueo a todos los campos
            setReadOnly(inputNombre, false);
            setReadOnly(inputPat, false);
            setReadOnly(inputMat, false);
            setReadOnly(inputRFC, false);
            setReadOnly(inputEmail, false);
            setReadOnly(inputDepto, false);
            setReadOnly(inputPuesto, false);
            
            // Limpiar valores (solo si no estamos en modo edición o si no hay old data)
            if(!checkAnfitrion.checked || (checkAnfitrion.checked && selectorUsuario.value === "")) {
                if(inputNombre) inputNombre.value = "";
                if(inputPat) inputPat.value = "";
                if(inputMat) inputMat.value = "";
                if(inputDepto) inputDepto.value = "";
                if(inputPuesto) inputPuesto.value = "";
                if(inputRFC) inputRFC.value = "";
                if(inputEmail) inputEmail.value = "";
            }

            if(inputEmail && emailHelper) emailHelper.style.display = 'none';
            if(hiddenUserId) hiddenUserId.value = "";

            // Limpiar campos laborales si se oculta la sección
            if(inputDepto) inputDepto.value = "";
            if(inputPuesto) inputPuesto.value = "";
        }


        // --- LÓGICA DE FACTURACIÓN (DESPLIEGUE DEL MENÚ) ---
        // Esta función ahora muestra/oculta el bloque de detalles de la factura.
        function toggleFactura() {
            if (checkFactura && billingDetails) {
                if (checkFactura.checked) {
                    billingDetails.style.display = 'block';
                } else {
                    billingDetails.style.display = 'none';
                }
            }
        }
        
        // Listener para el checkbox de Factura: establece que el usuario lo ha cambiado
        if (checkFactura) {
             checkFactura.addEventListener('change', function() {
                this.dataset.userChanged = 'true'; // El usuario ha interactuado
                toggleFactura();
            });
        }

        // Listener para el selector de concepto: actualiza el monto
        if (conceptoSelect) {
            conceptoSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const amount = selectedOption.getAttribute('data-amount');
                
                if (montoVisible && montoHidden) {
                    if (amount) {
                        // Formatear el monto para visualización
                        montoVisible.value = '$ ' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        montoHidden.value = amount; // Valor limpio para el backend
                    } else {
                        montoVisible.value = '$ 0.00';
                        montoHidden.value = '';
                    }
                }
            });
        }


        // --- LÓGICA ANFITRION (TOGGLE PRINCIPAL) ---
        if (checkAnfitrion && seccionLaboral) {
            function toggleAnfitrion() {
                if (checkAnfitrion.checked) {
                    // Caso Anfitrión: Mostrar datos laborales y buscador
                    seccionLaboral.style.display = 'block';
                    if (containerBuscador) containerBuscador.style.display = 'block';
                    
                    // Si es trabajador, desmarcamos factura por defecto y la habilitamos
                    if (checkFactura) {
                        checkFactura.disabled = false; // Habilitar para que pueda desmarcarla
                        
                        // Si el usuario no la ha cambiado manualmente, la desmarcamos (por defecto)
                        if (!checkFactura.dataset.userChanged) {
                            checkFactura.checked = false;
                            toggleFactura(); 
                        }
                    }
                    
                } else {
                    // Caso Estudiante Regular: Ocultar y Forzar Factura OBLIGATORIA
                    seccionLaboral.style.display = 'none';
                    if (containerBuscador) containerBuscador.style.display = 'none';
                    
                    // 🚨 FORZAR FACTURA OBLIGATORIA Y BLOQUEAR 🚨
                    if (checkFactura) {
                        checkFactura.checked = true; // Se marca obligatoriamente
                        checkFactura.dataset.userChanged = 'false'; // Reseteamos, esto no es cambio de usuario
                        toggleFactura(); // 🚨 Esto despliega el menú de detalles de facturación 🚨
                    }

                    // Limpieza y desbloqueo de campos personales/laborales
                    limpiarCamposPersonales();
                }
            }

            // Listener Anfitrión
            checkAnfitrion.addEventListener('change', toggleAnfitrion);
            
            // Ejecución inicial para aplicar el estado al cargar la página
            toggleAnfitrion();
        }


        // --- LÓGICA AUTOCOMPLETADO (Solo si es Anfitrión y selecciona) ---
        if (selectorUsuario) {
            selectorUsuario.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                const rfcValue = opt.getAttribute('data-rfc'); 
                
                if (this.value) {
                    // 1. APLICACIÓN DE VALORES
                    hiddenUserId.value = this.value;
                    if(inputNombre) inputNombre.value = opt.getAttribute('data-nombre');
                    if(inputPat) inputPat.value = opt.getAttribute('data-apellido_p');
                    if(inputMat) inputMat.value = opt.getAttribute('data-apellido_m');
                    if(inputTel) inputTel.value = opt.getAttribute('data-telefono');
                    if(inputDepto) inputDepto.value = opt.getAttribute('data-department');
                    if(inputPuesto) inputPuesto.value = opt.getAttribute('data-workstation');

                    // RFC
                    if (inputRFC) { 
                        inputRFC.value = rfcValue || ""; 
                    }
                    
                    // Email y Helper
                    if(inputEmail) {
                        inputEmail.value = opt.getAttribute('data-email');
                        if(emailHelper) emailHelper.style.display = 'block';
                    }
                    
                    // 2. APLICAR BLOQUEO
                    setReadOnly(inputNombre, true);
                    setReadOnly(inputPat, true);
                    setReadOnly(inputMat, true);
                    setReadOnly(inputRFC, true);
                    setReadOnly(inputEmail, true);
                    setReadOnly(inputDepto, true);
                    setReadOnly(inputPuesto, true);

                } else {
                    // Deselección: Limpia y desbloquea
                    limpiarCamposPersonales(); 
                    // Necesitamos re-aplicar el toggleAnfitrion para asegurar que los campos laborales se oculten/muestren correctamente.
                    toggleAnfitrion();
                }
            });
        }


        // --- LÓGICA CÁLCULO DE EDAD ---
        if (inputFechaNac && inputEdad) {
            function calcularEdad() {
                const fechaNac = inputFechaNac.value;
                if (fechaNac) {
                    const birthDate = new Date(fechaNac);
                    const today = new Date();
                    let age = today.getFullYear() - birthDate.getFullYear();
                    const monthDifference = today.getMonth() - birthDate.getMonth();
                    
                    if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    inputEdad.value = age;
                } else {
                    inputEdad.value = '';
                }
            }

            inputFechaNac.addEventListener('change', calcularEdad);
            // Ejecutar al inicio por si hay un valor pre-cargado
            calcularEdad();
        }

    }
    ejecutarLogicaInscripcion();

</script>
    
@endif
@endsection