@extends('layouts.app')
@section('content')

<div id="umi-app-view" style="padding: 20px;">

    {{-- TÍTULO --}}
    <div class="umi-header" style="margin-bottom: 20px;">
        <h1 style="color: #333;">GESTIÓN DE MATRÍCULAS Y DOCUMENTACIÓN SEP</h1>
    </div>

    {{-- TOOLBAR --}}
    <div class="umi-toolbar" style="display: flex; justify-content: space-between; margin-bottom: 20px;">
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
                {{-- Mantener la búsqueda al filtrar --}}
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                
                <select name="filter_status" onchange="this.form.submit()" style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    <option value="pagados" {{ request('filter_status') == 'pagados' ? 'selected' : '' }}>Solo Pagados (Listos para SEP)</option>
                    <option value="todos" {{ request('filter_status') == 'todos' ? 'selected' : '' }}>Todos los Aspirantes</option>
                </select>
            </form>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="umi-table-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="umi-table-scroll" style="overflow-x: auto; min-height: 300px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: #f4f6f9; border-bottom: 2px solid #ddd;">
                    <tr>
                        <th style="padding: 12px; text-align: left;">Aspirante</th>
                        <th style="padding: 12px; text-align: left;">Carrera</th>
                        <th style="padding: 12px; text-align: center;">Status Pago</th>
                        <th style="padding: 12px; text-align: center;">Documentación</th>
                        <th style="padding: 12px; text-align: center;">Asignación Matrícula</th>
                        <th style="padding: 12px; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody class="data-table-body">
                    @forelse ($dataList as $student)
                        <tr style="border-bottom: 1px solid #eee;">
                            {{-- 1. Datos del Aspirante --}}
                            <td style="padding: 12px;">
                                <strong>
                                    {{ $student->nombre }} {{ $student->apellido_paterno }} {{ $student->apellido_materno }}
                                </strong>
                                <br>
                                <small style="color: #666;">{{ $student->email }}</small>
                            </td>
                            
                            {{-- Usamos '?' para evitar error si no tiene perfil académico --}}
                            <td style="padding: 12px;">{{ $student->academicProfile?->career?->name ?? 'Sin Carrera' }}</td>

                            {{-- 2. Validación de Pago --}}
                            <td style="padding: 12px; text-align: center;">
                                @php
                                    $pagoStatus = $student->billing_status ?? 'Pendiente'; 
                                    $colorPago = $pagoStatus === 'Pagado' ? '#27ae60' : '#e74c3c';
                                @endphp
                                <span style="color: {{ $colorPago }}; font-weight: bold; border: 1px solid {{ $colorPago }}; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem;">
                                    {{ $pagoStatus }}
                                </span>
                            </td>

                            {{-- 3. Documentación --}}
                            <td style="padding: 12px; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    {{-- Iconos visuales --}}
                                    <i class="fa-solid fa-file-certificate" style="font-size: 1.2rem; {{ $student->doc_certificado ? 'color:#2980b9' : 'color:#ddd' }}" title="Certificado"></i>
                                    <i class="fa-solid fa-id-card" style="font-size: 1.2rem; {{ $student->doc_acta ? 'color:#2980b9' : 'color:#ddd' }}" title="Acta"></i>
                                    <i class="fa-solid fa-passport" style="font-size: 1.2rem; {{ $student->doc_curp ? 'color:#2980b9' : 'color:#ddd' }}" title="CURP"></i>
                                </div>
                            </td>

                            {{-- 4. INPUT DE MATRÍCULA (Corrección del Formulario) --}}
                            <td style="padding: 12px; text-align: center;">
                                {{-- ¡AQUÍ ESTABA EL ERROR! Agregamos el ID al form --}}
                                <form id="form-matricula-{{ $student->id }}" 
                                      action="{{ route('escolar.matriculas.update', $student->id) }}" 
                                      method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    @if($pagoStatus === 'Pagado')
                                        <input type="text" name="matricula" 
                                               value="{{ $student->academicProfile?->matricula }}" 
                                               placeholder="Ej. 2025-001"
                                               style="padding: 5px; border: 1px solid #2980b9; border-radius: 4px; width: 120px; text-align: center; font-weight: bold;">
                                    @else
                                        <span style="color: #95a5a6; font-size: 0.8rem;">
                                            <i class="fa-solid fa-lock"></i> Requiere Pago
                                        </span>
                                    @endif
                                </form>
                            </td>

                            {{-- Botón de Guardar (Fuera del form, referenciando por ID) --}}
                            <td style="padding: 12px; text-align: center;">
                                @if($pagoStatus === 'Pagado')
                                    <button type="submit" form="form-matricula-{{ $student->id }}" class="umi-btn" style="background: #2980b9; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">
                                        <i class="fa-solid fa-save"></i> Guardar
                                    </button>
                                @else
                                    <button disabled style="opacity: 0.5; cursor: not-allowed; border: none; background: transparent;">
                                        <i class="fa-solid fa-save" style="color: #ccc; font-size: 1.2rem;"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fa-solid fa-users-slash" style="font-size: 2rem; margin-bottom: 10px;"></i><br>
                                No se encontraron aspirantes que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
     
    </div>
</div>
@endsection