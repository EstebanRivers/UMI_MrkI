@extends('layouts.app')

@section('title', 'Lista de Alumnos - ' . session('active_institution_name'))

@section('content')

{{-- El ID "umi-app-view" es la clave para que el CSS de arriba aplique solo aquí --}}
<div id="umi-app-view">

    {{-- TÍTULO (Fijo) --}}
    <div class="umi-header">
        <h1>LISTA DE ALUMNOS</h1>
    </div>

    {{-- TOOLBAR (Fija) --}}
    <div class="umi-toolbar">
        <div class="umi-search-wrapper">
            <i class="fa-solid fa-magnifying-glass umi-search-icon"></i>
            {{-- Formulario de búsqueda funcional --}}
            <form action="{{ request()->url() }}" method="GET" style="width: 100%;">
                <input type="text" name="search" id="search" class="umi-search-input" 
                        placeholder="Buscar por Nombre, Matrícula o Correo..." 
                        value="{{ request('search') }}">
            </form>
        </div>

        {{-- Botones de Acción (Con estilos corregidos) --}}
        <button class="umi-btn-secondary">
            <i class="fa-solid fa-file-export"></i> Exportar
        </button>

        <button class="umi-btn-secondary">
            <i class="fa-solid fa-file-import"></i> Importar
        </button>

        @if(request()->routeIs('control.*'))
            <button class="umi-btn-secondary">
                <i class="fa-solid fa-file-import"></i> Importar
            </button>
        @endif
        
        {{-- Botón directo a Inscripción --}}
        <a href="{{ route('escolar.inscripcion.create') }}" class="umi-btn" >
            <i class="fa-solid fa-plus"></i> Nuevo Registro
        </a>
    </div>

    {{-- CARD QUE CONTIENE LA TABLA (Área de crecimiento flexible) --}}
    <div class="umi-table-card">
        <div class="umi-table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Carrera</th>
                        <th style="text-align:center">Matrícula</th>
                        <th>Nombre</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>
                        <th style="text-align:center">Status / Progreso</th>
                        <th style="text-align:center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="data-table-body">
                    @forelse ($dataList as $user)
                        <tr>
                            <td style="font-weight: 600; color: #555;">
                                {{ $user->academicProfile?->career?->name ?? 'Sin Asignar' }}
                            </td>
                            
                            {{-- Columna Matrícula --}}
                            <td style="text-align:center">
                                @if(!empty($user->academicProfile->matricula))
                                    <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-weight: bold; border: 1px solid #c8e6c9;">
                                        {{ $user->academicProfile->matricula }}
                                    </span>
                                @else
                                    <span style="color: #999; font-style: italic; background: #f5f5f5; padding: 2px 6px; border-radius: 4px;">
                                        Pendiente
                                    </span>
                                @endif
                            </td>

                            <td style="font-weight: 700;">
                                {{ $user->nombre }}
                            </td>
                            <td style="font-weight: 700;">{{ $user->apellido_paterno }}</td>
                            <td style="font-weight: 700;">{{ $user->apellido_materno }}</td>
                            
                            {{-- Status con Lógica de Negocio Visual --}}
                            <td style="text-align:center">
                                @php
                                    $status = $user->academicProfile->status ?? 'Aspirante';
                                    $matricula = $user->academicProfile->matricula ?? null;
                                    
                                    $statusColor = match($status) {
                                        'Alumno Activo' => '#27ae60', // Verde
                                        'Alumno Inactivo' => '#e74c3c', // Rojo
                                        'Baja' => '#7f8c8d', // Gris
                                        'Egresado' => '#3498db', // Azul
                                        default => '#f39c12', // Naranja (Aspirante)
                                    };
                                @endphp

                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span style="color: {{ $statusColor }}; font-weight: bold; border: 1px solid {{ $statusColor }}; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem; width: fit-content;">
                                        {{ $status }}
                                    </span>

                                    {{-- INDICADORES DE FLUJO --}}
                                    @if($status === 'Aspirante')
                                        @if($matricula)
                                            <small style="color: #d35400; font-weight: 600;">
                                                <i class="fa-solid fa-key"></i> Falta Contraseña
                                            </small>
                                        @else
                                            <small style="color: #7f8c8d; font-size: 0.75rem;">
                                                Esperando Matrícula
                                            </small>
                                        @endif
                                    @elseif($status === 'Alumno Inactivo')
                                        <small style="color: #c0392b;">Requiere Pago</small>
                                    @endif
                                </div>
                            </td>

                            <td style="text-align:center">
                                <div class="actions-row" style="justify-content: center;">
                                    
                                    {{-- 1. BOTÓN OJO (MODIFICADO) --}}
                                    <button type="button" class="btn-icon" title="Ver Expediente" style="border:none; background:none;"
                                        onclick="openStudentDetails(
                                            '{{ $user->nombre }} {{ $user->apellido_paterno }} {{ $user->apellido_materno }}',
                                            '{{ $user->email }}',
                                            '{{ $user->telefono ?? 'N/A' }}',
                                            '{{ $user->academicProfile->career->name ?? 'Sin Carrera' }}',
                                            '{{ $user->academicProfile->semestre ?? '1' }}',
                                            '{{ $user->academicProfile->status ?? 'Pendiente' }}',
                                            '{{ $user->academicProfile->matricula ?? 'No Asignada' }}',
                                            // URLs de Documentos
                                            '{{ $user->academicProfile->doc_acta_nacimiento ? Storage::url($user->academicProfile->doc_acta_nacimiento) : '' }}',
                                            '{{ $user->academicProfile->doc_certificado_prepa ? Storage::url($user->academicProfile->doc_certificado_prepa) : '' }}',
                                            '{{ $user->academicProfile->doc_curp ? Storage::url($user->academicProfile->doc_curp) : '' }}',
                                            '{{ $user->academicProfile->doc_ine ? Storage::url($user->academicProfile->doc_ine) : '' }}'
                                        )">
                                        <img src="{{ asset('images/icons/eye-solid-full.svg') }}" alt="Ver">
                                    </button>
                                    
                                    {{-- 2. EDITAR --}}
                                    <a href="{{ request()->routeIs('control.*') ? route('control.students.edit', $user->id) : route('escolar.students.edit', $user->id) }}" 
                                        class="btn-icon"
                                        title="Editar / Asignar Contraseña">
                                        <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
                                    </a>
                                    
                                    {{-- 3. ELIMINAR --}}
                                    <form 
                                        method="POST" 
                                        action="{{ route(request()->routeIs('control.*') ? 'control.students.destroy' : 'escolar.students.destroy', $user->id) }}" 
                                        onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $user->nombre }}? Esta acción es irreversible.');" 
                                        class="inline-form" style="display:inline;"
                                    >
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon btn-icon--danger" title="Eliminar Alumno">
                                            <img src="{{ asset('images/icons/Vector.svg') }}" alt="Eliminar">
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
                                No se encontraron alumnos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL 1: DETALLES DEL ALUMNO (EXPEDIENTE)                 --}}
{{-- ========================================================= --}}
<div id="studentDetailsModal" class="modal-overlay" style="display: none; z-index: 9999;">
    <div class="modal-container expediente-modal"> 
        <div class="modal-header">
            <h3>📂 Expediente del Alumno</h3>
            <button type="button" class="modal-close" onclick="closeStudentDetails()">&times;</button>
        </div>
        
        <div class="modal-body-scroll">
            {{-- Encabezado del Alumno --}}
            <div class="student-summary">
                <div class="avatar-placeholder">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="student-info-header">
                    <h2 id="modalName" style="margin:0;">-</h2>
                    <span id="modalStatusBadge" class="badge-status">-</span>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

            <div class="details-grid">
                {{-- Columna Izquierda: Datos --}}
                <div class="details-column">
                    <h4 style="color:#666; border-bottom:1px solid #eee; padding-bottom:5px; margin-bottom:15px;">
                        <i class="fa-solid fa-id-card"></i> Información Personal
                    </h4>
                    <div class="detail-item">
                        <label>Matrícula:</label> <span id="modalMatricula" style="font-weight: bold; color: #2c3e50;">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label> <span id="modalEmail">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Teléfono:</label> <span id="modalPhone">-</span>
                    </div>
                    
                    <h4 style="color:#666; border-bottom:1px solid #eee; padding-bottom:5px; margin-bottom:15px; margin-top:20px;">
                        <i class="fa-solid fa-graduation-cap"></i> Académico
                    </h4>
                    <div class="detail-item">
                        <label>Carrera:</label> <span id="modalCareer">-</span>
                    </div>
                    <div class="detail-item">
                        <label>Semestre:</label> <span id="modalSemester">-</span>
                    </div>
                </div>

                {{-- Columna Derecha: Documentos --}}
                <div class="details-column">
                    <h4 style="color:#666; border-bottom:1px solid #eee; padding-bottom:5px; margin-bottom:15px;">
                        <i class="fa-solid fa-folder-open"></i> Documentación
                    </h4>
                    <div class="docs-list">
                        <button id="btnDocActa" class="doc-btn hidden"><i class="fa-solid fa-file-pdf"></i> Acta de Nacimiento</button>
                        <button id="btnDocCert" class="doc-btn hidden"><i class="fa-solid fa-file-certificate"></i> Certificado Prepa</button>
                        <button id="btnDocCurp" class="doc-btn hidden"><i class="fa-solid fa-passport"></i> CURP</button>
                        <button id="btnDocIne" class="doc-btn hidden"><i class="fa-solid fa-id-card"></i> INE</button>
                        
                        <div id="noDocsMsg" class="no-docs" style="display:none;">
                            No hay documentos digitales cargados.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- MODAL 2: VISOR DE DOCUMENTOS (ENCIMA DEL PRIMER MODAL)    --}}
{{-- ========================================================= --}}
<div id="docViewerModal" class="modal-overlay" style="display: none; z-index: 10000;">
    <div class="modal-container" style="height: 90vh; width: 80%; max-width: 1000px;">
        <div class="modal-header" style="background: #333; color: white; border-radius: 8px 8px 0 0;">
            <h3 id="docViewerTitle" style="margin: 0; font-size: 1.1rem;">Visualizando Documento</h3>
            <button type="button" class="modal-close" onclick="closeDocViewer()" style="color: white;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0; height: 100%; background: #525659;">
            <iframe id="docViewerFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
        </div>
    </div>
</div>

{{-- ESTILOS Y SCRIPTS --}}
<style>
    /* Botones Toolbar (Estilo corregido) */
    .umi-btn-secondary{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    /* Se asume que var(--umi-blue-dark) es el color institucional */
    background-color: var(--umi-blue-dark);
    color: #FFFFFF;
    border: none;
    font-weight: 500; 
    font-size: 16px; 
    border-radius: 50px; 
    padding: 10px 30px; 
    cursor: pointer;
    transition: background-color 0.2s, transform 0.1s;
    text-decoration: none;
    box-shadow: 0 4px 6px rgba(34, 63, 112, 0.2);
    white-space: nowrap; 
}
.umi-btn-secondary:hover { background-color: #1a3055; transform: translateY(-1px); }

    /* Estilos generales de los modales (overlay, cerrar, etc.) */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; backdrop-filter: blur(2px); }
    
    /* ESTILO BASE/GRANDE: Aplica al Modal del Visor de Documentos (y como fallback) */
    .modal-container { 
        background: white; 
        width: 90%; 
        max-width: 800px; 
        border-radius: 12px; 
        display: flex; 
        flex-direction: column; 
        max-height: 90vh; 
        box-shadow: 0 15px 30px rgba(0,0,0,0.3); 
    }

    /* ESTILO ESPECÍFICO: Aplica solo al Modal del Expediente (clase: expediente-modal) */
    .expediente-modal {
        max-width: 600px; /* Tamaño reducido para el Expediente */
        max-height: 80vh; 
    }
    
    /* >>>>>>>>>>>>>>> NUEVA REGLA PARA EL TÍTULO DEL MODAL <<<<<<<<<<<<<<< */
    /* Se aplica el color azul oscuro institucional al título H3 del Expediente */
    .expediente-modal .modal-header h3 {
        color: var(--umi-blue-dark, #223F70); /* Usa la variable si existe, sino un azul oscuro seguro */
        font-weight: 700;
        margin: 0;
    }
    
    /* Media Query para pantallas pequeñas */
    @media (max-width: 650px) {
        .details-grid {
            grid-template-columns: 1fr; 
        }
        .expediente-modal {
            max-height: 90vh; 
            width: 95%;
        }
    }
    
    /* Estilos de elementos internos restantes */
    .modal-header { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; border-radius: 12px 12px 0 0; }
    .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666; }
    .modal-body-scroll { padding: 25px; overflow-y: auto; flex: 1; }
    .modal-footer { padding: 15px 20px; border-top: 1px solid #eee; text-align: right; background: #f8f9fa; border-radius: 0 0 12px 12px; }
    
    .btn-secondary-modal { background: #e0e0e0; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; color: #333; font-weight: 600; }
    .btn-secondary-modal:hover { background: #d0d0d0; }

    .doc-btn { display: flex; align-items: center; width: 100%; padding: 12px 15px; margin-bottom: 10px; background: #fbfbfb; color: #2c3e50; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: left; transition: all 0.2s; }
    .doc-btn:hover { background: #e3f2fd; border-color: #3498db; color: #223F70; transform: translateX(5px); }
    .doc-btn i { margin-right: 12px; font-size: 1.2rem; color: #e74c3c; }
    .hidden { display: none !important; }
    .no-docs { text-align: center; color: #aaa; font-style: italic; padding: 15px; border: 1px dashed #eee; border-radius: 8px; }

    /* Badges Status */
    .badge-status { border: 1px solid; padding: 2px 8px; border-radius: 12px; font-weight: bold; font-size: 0.8rem; }
    .badge-green { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
    .badge-orange { background: #fff3e0; color: #ef6c00; border-color: #ffe0b2; }
    .badge-gray { background: #f5f5f5; color: #616161; border-color: #e0e0e0; }

    .student-summary { display: flex; align-items: center; gap: 15px; }
    .avatar-placeholder { width: 50px; height: 50px; background: #e0e0e0; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 20px; color: white; }
    
    /* ALINEACIÓN HEADER */
    .student-info-header {
        display: flex; 
        align-items: center; 
        gap: 15px; 
    }
    .student-info-header h2 {
        margin: 0; font-size: 1.4rem; color: #2c3e50;
    }

    .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
    .detail-item { margin-bottom: 12px; }
    .detail-item label { font-weight: 600; color: #7f8c8d; width: 80px; display: inline-block; }
    
</style>
{{-- SCRIPTS --}}
<script>
    // --- 1. FUNCIONES DEL VISOR (NIVEL 2) - MOVIDAS AL ÁMBITO GLOBAL ---
    function openDocViewer(url, title) {
        if (!url || url === '') return;
        document.getElementById('docViewerFrame').src = url;
        document.getElementById('docViewerTitle').innerText = title;
        document.getElementById('docViewerModal').style.display = 'flex';
    }

    function closeDocViewer() {
        document.getElementById('docViewerModal').style.display = 'none';
        document.getElementById('docViewerFrame').src = ""; 
    }

    // --- 2. FUNCIONES DEL EXPEDIENTE (NIVEL 1) - MOVIDAS AL ÁMBITO GLOBAL ---
    function openStudentDetails(name, email, phone, career, semester, status, matricula, docActa, docCert, docCurp, docIne) {
        
        // Llenar textos
        document.getElementById('modalName').innerText = name;
        document.getElementById('modalEmail').innerText = email;
        document.getElementById('modalPhone').innerText = phone;
        document.getElementById('modalCareer').innerText = career;
        document.getElementById('modalSemester').innerText = semester;
        document.getElementById('modalMatricula').innerText = matricula;
        
        const badge = document.getElementById('modalStatusBadge');
        badge.innerText = status;
        badge.className = 'badge-status'; 
        if(status === 'Alumno Activo') badge.classList.add('badge-green');
        else if(status === 'Aspirante') badge.classList.add('badge-orange');
        else badge.classList.add('badge-gray');

        // Configurar Botones
        let docsCount = 0;
        const configureBtn = (btnId, url, title) => {
            const btn = document.getElementById(btnId);
            if (url && url.trim() !== '') {
                btn.classList.remove('hidden');
                btn.onclick = function() { openDocViewer(url, title); };
                docsCount++;
            } else {
                btn.classList.add('hidden');
            }
        };

        configureBtn('btnDocActa', docActa, 'Acta de Nacimiento');
        configureBtn('btnDocCert', docCert, 'Certificado Preparatoria');
        configureBtn('btnDocCurp', docCurp, 'CURP');
        configureBtn('btnDocIne', docIne, 'INE');

        document.getElementById('noDocsMsg').style.display = (docsCount === 0) ? 'block' : 'none';
        document.getElementById('studentDetailsModal').style.display = 'flex';
    }

    function closeStudentDetails() {
        document.getElementById('studentDetailsModal').style.display = 'none';
    }
    
    // --- 3. FUNCIÓN DE INICIALIZACIÓN (Mantiene la ejecución en el scope local) ---
    function ejecutarLogicaListaAlumnos() {
        // Aquí iría cualquier listener o inicialización de variables que solo necesiten ejecutarse una vez, 
        // pero NO las definiciones de las funciones que deben ser globales.
        console.log("Listeners inicializados."); 
    }
    
    // --- 4. PUNTO DE ENTRADA ---
    document.addEventListener('DOMContentLoaded', ejecutarLogicaListaAlumnos);
    
    // Si estás usando Livewire/AJAX, también deberías inicializar:
    if (window.Livewire) {
        window.Livewire.hook('message.processed', () => {
            ejecutarLogicaListaAlumnos();
        });
    }

</script>

@endsection