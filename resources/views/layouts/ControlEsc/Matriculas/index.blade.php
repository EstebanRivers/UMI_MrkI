@extends('layouts.app')
@section('content')


<div id="umi-app-view" style="padding: 20px;">

    {{-- TÍTULO --}}
    <div class="umi-header" style="margin-bottom: 20px;">
        <h1 style="color: #333; font-size: 1.8rem;">GESTIÓN DE MATRÍCULAS Y DOCUMENTACIÓN SEP</h1>
    </div>

    {{-- MENSAJES DE ÉXITO (Desaparece en 3 segundos) --}}
    @if(session('success'))
        <div id="success-alert" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb; text-align: center;">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- TOOLBAR --}}
    <div class="umi-toolbar" style="display: flex; justify-content: space-between; margin-bottom: 20px; align-items: center;">
        <div class="umi-search-wrapper" style="flex: 1; margin-right: 15px;">
            <form action="{{ request()->url() }}" method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="search" class="umi-search-input" 
                       placeholder="Buscar por Nombre o Correo..." 
                       value="{{ request('search') }}"
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </form>
        </div>
        
        <div class="filter-group">
            <form action="{{ request()->url() }}" method="GET">
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                
                <select name="filter_status" onchange="this.form.submit()" style="padding: 10px; border-radius: 5px; border: 1px solid #ccc; background-color: white; cursor: pointer;">
                    <option value="todos" {{ request('filter_status') == 'todos' ? 'selected' : '' }}>Todos los Aspirantes</option>
                    <option value="pagados" {{ request('filter_status') == 'pagados' ? 'selected' : '' }}>Solo Pagados (Listos para MATRÍCULA)</option>
                </select>
            </form>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="umi-table-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="umi-table-scroll" style="overflow-x: auto; min-height: 300px;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed;">
                <thead style="background-color: #223F70; color: white;">
                    <tr>
                        <th style="padding: 12px; text-align: center; width: 25%;">Aspirante</th>
                        <th style="padding: 12px; text-align: center; width: 20%;">Carrera</th>
                        <th style="padding: 12px; text-align: center; width: 10%;">Status Pago</th>
                        <th style="padding: 12px; text-align: center; width: 20%;">Documentación</th>
                        <th style="padding: 12px; text-align: center; width: 15%;">Asignación Matrícula</th>
                        <th style="padding: 12px; text-align: center; width: 10%;">Acción</th>
                    </tr>
                </thead>
                <tbody class="data-table-body">
                    @forelse ($dataList as $student)
                        <tr style="border-bottom: 1px solid #eee;">
                            
                            {{-- 1. Datos del Aspirante --}}
                            <td style="padding: 12px; vertical-align: middle; border-bottom: 1px solid #eee; text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                    <strong style="color: #333; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                                        {{ $student->nombre }} {{ $student->apellido_paterno }} {{ $student->apellido_materno }}
                                    </strong>
                                    <small style="color: #777; margin-top: 4px;">
                                        <i class="fa-regular fa-envelope"></i> {{ $student->email }}
                                    </small>
                                </div>
                            </td>
                            
                            {{-- 2. Carrera --}}
                            <td style="padding: 12px; vertical-align: middle; border-bottom: 1px solid #eee; text-align: center;">
                                <span style="display: block; line-height: 1.4; font-size: 0.9rem;">
                                    {{ $student->academicProfile?->career?->name ?? 'Sin Carrera Asignada' }}
                                </span>
                            </td>

                            {{-- 3. Validación de Pago --}}
                            <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
                                @php
                                    $pagoStatus = $student->billing_status ?? 'Pendiente'; 
                                    $colorPago = $pagoStatus === 'Pagado' ? '#27ae60' : '#e74c3c';
                                    $bgPago = $pagoStatus === 'Pagado' ? '#eafaf1' : '#fdedec';
                                @endphp
                                <span style="color: {{ $colorPago }}; background-color: {{ $bgPago }}; font-weight: bold; border: 1px solid {{ $colorPago }}; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; display: inline-block;">
                                    {{ $pagoStatus }}
                                </span>
                            </td>

                            {{-- 4. DOCUMENTACIÓN (CON VISOR MODAL Y VALIDACIÓN DE PAGO) --}}
                            <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
                                <div style="background: #f9f9f9; padding: 10px; border-radius: 6px; border: 1px dashed #ccc; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    
                                    {{-- ÍCONOS --}}
                                    <div style="display: flex; justify-content: center; gap: 10px; width: 100%;">
                                        <i class="fa-solid fa-file-certificate" style="font-size: 1.1rem; {{ $student->doc_certificado ? 'color:#27ae60' : 'color:#bdc3c7' }}" title="Certificado"></i>
                                        <i class="fa-solid fa-id-card" style="font-size: 1.1rem; {{ $student->doc_acta ? 'color:#27ae60' : 'color:#bdc3c7' }}" title="Acta"></i>
                                        <i class="fa-solid fa-passport" style="font-size: 1.1rem; {{ $student->doc_curp ? 'color:#27ae60' : 'color:#bdc3c7' }}" title="CURP"></i>
                                    </div>
                                    <hr style="width: 80%; border: 0; border-top: 1px solid #eee; margin: 2px 0;">

                                    {{-- VALIDACIÓN PRINCIPAL: SOLO SI ESTÁ PAGADO PUEDE INTERACTUAR --}}
                                    @if($pagoStatus === 'Pagado')

                                        @if($student->academicProfile && $student->academicProfile->documentoSEP_path)
                                            
                                            {{-- A) SI YA EXISTE ARCHIVO --}}
                                            <div style="width: 100%;">
                                                
                                                {{-- BOTÓN QUE ABRE EL MODAL --}}
                                                <a href="javascript:void(0)" 
                                                   onclick="openDocViewer('{{ asset('storage/' . $student->academicProfile->documentoSEP_path) }}', '{{ $student->nombre }} {{ $student->apellido_paterno }}')"
                                                   class="umi-btn" 
                                                   style="background-color: #223F70; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; display: block; width: 100%; box-sizing: border-box; text-align: center; margin-bottom: 5px; font-weight: bold;">
                                                    <i class="fa-solid fa-eye"></i> VER DOCUMENTO
                                                </a>

                                                {{-- Formulario para cambiar --}}
                                                <form id="form-doc-{{ $student->id }}" action="{{ route('escolar.documentacion.upload', $student->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="file" 
                                                           id="file-upload-{{ $student->id }}" 
                                                           name="documento_pdf" 
                                                           class="pdf-uploader" 
                                                           data-form-id="form-doc-{{ $student->id }}"
                                                           accept="application/pdf" 
                                                           style="display: none;">
                                                    
                                                    <label for="file-upload-{{ $student->id }}" style="cursor: pointer; color: #777; font-size: 0.75rem; text-decoration: underline; display: block; margin-top: 5px;">
                                                        <i class="fa-solid fa-rotate"></i> Cambiar archivo
                                                    </label>
                                                </form>
                                            </div>

                                        @else

                                            {{-- B) SI NO HAY ARCHIVO (PERO ESTÁ PAGADO) --}}
                                            <form id="form-doc-{{ $student->id }}" action="{{ route('escolar.documentacion.upload', $student->id) }}" method="POST" enctype="multipart/form-data" style="width: 100%;">
                                                @csrf
                                                <input type="file" 
                                                       id="file-upload-{{ $student->id }}" 
                                                       name="documento_pdf" 
                                                       class="pdf-uploader" 
                                                       data-form-id="form-doc-{{ $student->id }}"
                                                       accept="application/pdf" 
                                                       style="display: none;">

                                                <label for="file-upload-{{ $student->id }}" 
                                                       style="cursor: pointer; background: #e0e0e0; color: #333; padding: 8px 10px; border-radius: 4px; font-size: 0.8rem; border: 1px solid #ccc; font-weight: 600; display: block; width: 100%; box-sizing: border-box; text-align: center;">
                                                    <i class="fa-solid fa-cloud-arrow-up"></i> SUBIR PDF
                                                </label>
                                                <small style="display: block; color: #999; font-size: 0.7rem; margin-top: 3px;">(Max 10MB)</small>
                                            </form>

                                        @endif

                                    @else
                                        {{-- C) SI NO HA PAGADO: BLOQUEADO --}}
                                        <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #eee; font-size: 0.75rem; color: #95a5a6; text-align: center;">
                                            <i class="fa-solid fa-lock" style="font-size: 1.2rem; margin-bottom: 5px; display: block;"></i> 
                                            Pago Requerido
                                        </div>
                                    @endif

                                </div>
                            </td>

                            {{-- 5. INPUT MATRÍCULA --}}
                            <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
                                <form id="form-matricula-{{ $student->id }}" 
                                      action="{{ route('escolar.matriculas.update', $student->id) }}" 
                                      method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    @if($pagoStatus === 'Pagado')
                                        <input type="text" name="matricula" 
                                               value="{{ $student->academicProfile?->matricula }}" 
                                               placeholder="Ej. 2025-001"
                                               style="padding: 8px; border: 1px solid #223F70; border-radius: 4px; width: 100%; box-sizing: border-box; text-align: center; font-weight: bold; color: #223F70;">
                                    @else
                                        <div style="background: #f8f9fa; padding: 8px; border-radius: 4px; border: 1px solid #eee; font-size: 0.8rem; color: #95a5a6;">
                                            <i class="fa-solid fa-lock"></i> Pago Pendiente
                                        </div>
                                    @endif
                                </form>
                            </td>

                            {{-- 6. ACCIÓN --}}
                            <td style="padding: 12px; text-align: center; vertical-align: middle; border-bottom: 1px solid #eee;">
                                @if($pagoStatus === 'Pagado')
                                    <button type="submit" form="form-matricula-{{ $student->id }}" class="umi-btn" style="background: #223F70; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-size: 0.9rem; transition: background 0.3s; display: inline-flex; align-items: center; gap: 5px;">
                                        <i class="fa-solid fa-save"></i> Guardar
                                    </button>
                                @else
                                    <button disabled style="opacity: 0.4; cursor: not-allowed; border: 1px solid #ccc; background: #eee; padding: 8px 15px; border-radius: 4px; color: #777; display: inline-flex; align-items: center; gap: 5px;">
                                        <i class="fa-solid fa-ban"></i> Bloqueado
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 50px; color: #666; background-color: #fafafa;">
                                <i class="fa-solid fa-users-slash" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
                                <p style="font-size: 1.1rem; margin: 0;">No se encontraron aspirantes que coincidan con los filtros.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px;">
            {{ $dataList->appends(request()->query())->links() }}
        </div>
    </div>
</div>

{{-- MODAL HTML --}}
<div id="docViewerModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="docViewerTitle" style="margin: 0; font-size: 1rem; font-weight: normal;">Visualizando Documento</h3>
            <button type="button" class="modal-close" onclick="closeDocViewer()">&times;</button>
        </div>
        <div class="modal-body" style="flex: 1; background: #525659; position: relative;">
            <iframe id="docViewerFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. OCULTAR MENSAJE DE ÉXITO AUTOMÁTICAMENTE (3 Segundos)
        const successAlert = document.getElementById('success-alert');
        if (successAlert) {
            setTimeout(function() {
                successAlert.style.opacity = '0'; // Desvanecer
                setTimeout(() => {
                    successAlert.style.display = 'none'; // Eliminar espacio
                }, 500); // Esperar a que termine la transición
            }, 3000); // 3000ms = 3 segundos
        }

        // 2. INICIALIZADOR DE UPLOADS (Auto-submit)
        const uploaders = document.querySelectorAll('.pdf-uploader');
        uploaders.forEach(input => {
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const label = document.querySelector(`label[for="${this.id}"]`);
                    label.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Subiendo...';
                    const formId = this.getAttribute('data-form-id');
                    document.getElementById(formId).submit();
                }
            });
        });

        // 3. CERRAR MODAL CON CLIC AFUERA
        document.getElementById('docViewerModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDocViewer();
            }
        });
    });

    // FUNCIONES DEL MODAL (Globales para que el onclick funcione)
    function openDocViewer(url, title) {
        document.getElementById('docViewerFrame').src = url;
        document.getElementById('docViewerTitle').innerText = 'Documento: ' + title;
        document.getElementById('docViewerModal').style.display = 'flex';
    }

    function closeDocViewer() {
        document.getElementById('docViewerModal').style.display = 'none';
        document.getElementById('docViewerFrame').src = ''; // Limpiar para detener carga
    }
</script>
@endsection