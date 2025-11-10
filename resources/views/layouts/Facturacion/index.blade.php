@extends('layouts.app')

@section('title', 'Facturación')

@section('content')

    {{-- ========================================================== --}}
    {{-- INICIO DE LA CORRECCIÓN: Modal movido al inicio de @section --}}
    {{-- ========================================================== --}}
    @if(Auth::user()->hasActiveRole('master') || Auth::user()->hasActiveRole('control_administrativo'))
    <div id="modalFactura" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Agregar Factura</h2>
            <form id="formFacturaModal" method="POST" action="{{ route('Facturacion.store') }}" enctype="multipart/form-data">
                 @csrf
                 <input type="hidden" id="modal_user_id" name="user_id" value="">
                
                 <label for="modal_period_id">Período:</label>
                <select id="modal_period_id" name="period_id" required>
                    <option value="" disabled selected>Selecciona un período...</option>
                    @if(isset($periods))
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}">{{ $period->name }}</option>
                        @endforeach
                    @endif
                </select>
                 
                <label for="modal_concepto">Concepto:</label>
                <input type="text" id="modal_concepto" name="concepto" required>
                 <label for="modal_monto">Monto (MXN):</label>
                <input type="number" id="modal_monto" name="monto" required step="0.01">
                <label for="modal_fecha">Fecha de Vencimiento:</label>
                <input type="date" id="modal_fecha" name="fecha" required>
                <label for="modal_archivo">Subir factura (PDF):</label>
                <input type="file" id="modal_archivo" name="archivo" accept=".pdf" required>
                @error('archivo')<div class="modal-error-message">{{ $message }}</div>@enderror
                <label for="modal_archivo_xml">Subir XML (Opcional):</label>
                <input type="file" id="modal_archivo_xml" name="archivo_xml" accept=".xml,text/xml">
                @error('archivo_xml')<div class="modal-error-message">{{ $message }}</div>@enderror
                <label for="modal_status">Estado:</label>
                <select id="modal_status" name="status" required>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Pagada">Pagada</option>
                </select>
                <button type="submit" class="guardar">Guardar Factura</button>
            </form>
        </div>
    </div>
    @endif
    {{-- --- FIN DEL BLOQUE DEL MODAL --- --}}


    <div class="main-header"> <h1>Facturación</h1> </div>

{{-- Formulario de Filtros (CORREGIDO) --}}
<form action="{{ route('Facturacion.index') }}" method="GET">
    <div class="filter-controls">
        
        <div class="filter-group">
            <input type="text" name="search" class="search-input" placeholder="Usuario, Concepto, No. Factura..." value="{{ request('search') }}">
            
            {{-- FILTRO DE TIPO DE USUARIO ELIMINADO --}}
            
            <div class="filter-item">
                <label for="status">Status (Factura)</label>
                <select id="status" name="status" class="filter-select">
                    <option value="">TODOS</option>
                    <option value="Pendiente" @selected(request('status') == 'Pendiente')>Pendiente</option>
                    <option value="Abonado" @selected(request('status') == 'Abonado')>Abonado</option>
                    <option value="Pagada" @selected(request('status') == 'Pagada')>Pagada</option>
                </select>
            </div>

            {{-- ESTE FILTRO AHORA SE MUESTRA SIEMPRE --}}
            @if(isset($periods))
            <div class="filter-item">
                <label for="period_id">Período</label>
                <select id="period_id" name="period_id" class="filter-select">
                    <option value="">Todos los Períodos</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>{{ $period->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
        
        <div class="action-group">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('Facturacion.index') }}" class="btn btn-secondary">Limpiar</a>
            <button type="button" class="btn export-btn">Exportar</button>
        </div>
    </div>
</form>


    {{-- Contenido Principal: Depende del Rol --}}
    <div class="scrollable-content-area">

        @if(isset($usersWithBillings)) 
            {{-- VISTA PARA MASTER Y CONTROL ADMIN: Acordeón de PERÍODOS --}}
            <h2>Usuarios por Período</h2>
            <div class="period-accordion">
                @forelse ($periods as $period)
                    @php
                        $usersInThisPeriod = $usersWithBillings->filter(function($user) use ($period) {
                            return $user->billings->where('period_id', $period->id)->isNotEmpty();
                        });
                    @endphp
                    
                    @if($usersInThisPeriod->isNotEmpty())
                        <details id="period-{{ $period->id }}">
                            <summary>
                                <span class="period-name">{{ $period->name }}</span>
                                <span class="period-info">{{ $usersInThisPeriod->count() }} usuarios con facturas</span>
                                <span class="arrow-icon">▼</span>
                            </summary>
                            <div class="period-details">
                                <div class="user-accordion">
                                    
                            @foreach ($usersInThisPeriod as $u)
                                @php
                                    // 1. Obtenemos todas las facturas del usuario para este período
                                    $allBillingsForPeriod = $u->billings->where('period_id', $period->id);

                                    // 2. ¡AQUÍ ESTÁ EL FILTRO NUEVO!
                                    // Si el filtro de status está activo...
                                    if (request()->filled('status')) {
                                        $statusFilter = request('status');
                                        
                                        // 3. Filtramos la colección ANTES de mostrarla
                                        $billingsForPeriod = $allBillingsForPeriod->filter(function($billing) use ($statusFilter) {
                                            
                                            // --- Re-calculamos el estatus (igual que en tu @php de abajo) ---
                                            $estatus = $billing->status;
                                            $totalPagado = $billing->payments->sum('monto');
                                            if ($estatus == 'Pendiente') {
                                                if ($totalPagado >= $billing->monto) { $estatus = 'Pagada'; } 
                                                elseif ($totalPagado > 0) { $estatus = 'Abonado'; }
                                            }
                                            // --- Fin del re-cálculo ---
                                            
                                            return $estatus === $statusFilter;
                                        });
                                    } else {
                                        // Si no hay filtro, mostramos todas
                                        $billingsForPeriod = $allBillingsForPeriod;
                                    }
                                @endphp
                                
                                <details id="user-anchor-{{ $u->id }}">
                                    <summary>
                                        <div>
                                            <span class="user-name">{{ $u->nombre }} {{ $u->apellido_paterno }}</span>
                                            <span class="user-email">{{ $u->email }}</span>
                                        </div>
                                        <div>
                                            <span class="user-role">{{ $u->roles->pluck('display_name')->join(', ') }}</span>
                                            <span class="arrow-icon">▼</span>
                                        </div>
                                    </summary>
                                    <div class="user-details">
                                        <button class="add-invoice-btn" data-user-id="{{ $u->id }}" data-user-name="{{ $u->nombre }} {{ $u->apellido_paterno }}" data-period-id="{{ $period->id }}">
                                            + Agregar Factura para {{ $u->nombre }}
                                        </button>
                                        
                                        <div class="table-container">
                                            {{-- Este @forelse ahora solo itera sobre las facturas filtradas --}}
                                            @forelse ($billingsForPeriod as $billing)
                                                @php
                                                    // (Esta lógica sigue siendo necesaria para mostrar la fila)
                                                    $estatus = $billing->status;
                                                    $estatusClase = strtolower($estatus);
                                                    $totalPagado = $billing->payments->sum('monto');
                                                    $saldo = $billing->monto - $totalPagado;

                                                    if ($estatus == 'Pendiente') {
                                                        if ($totalPagado >= $billing->monto) { $estatus = 'Pagada'; $estatusClase = 'pagada'; } 
                                                        elseif ($totalPagado > 0) { $estatus = 'Abonado'; $estatusClase = 'abonado'; }
                                                    }

                                                    $estatusIcono = 'circulo_rojo.png';
                                                    if ($estatus == 'Pagada') { $estatusIcono = 'circulo_verde.png'; } 
                                                    elseif ($estatus == 'Abonado') { $estatusIcono = 'circulo_amarillo.png'; }
                                                @endphp
                                                
                                                <table style="margin-bottom: 0; border-top: 1px solid #eee;">
                                                    {{-- ... (el resto de tu <table>, <tbody>, <tr>, etc.) ... --}}
                                                    <thead style="display: none;"><tr><th>...</th></tr></thead>
                                                    <tbody class="billing-item-tbody">
                                                        <tr class="billing-main-row">
                                                            <td width="15%">{{ $billing->factura_uid }}</td>
                                                            <td width="25%">{{ $billing->concepto }}</td>
                                                            <td width="15%">
                                                                ${{ number_format($billing->monto, 2) }}
                                                                @if($estatus == 'Abonado') <br><small style="color: #e8a800; font-weight: 600;">(Saldo: ${{ number_format($saldo, 2) }})</small> @endif
                                                            </td>
                                                            <td width="15%">{{ \Carbon\Carbon::parse($billing->fecha_vencimiento)->format('d/m/Y') }}</td>
                                                            <td width="15%"><div class="estado"><img src="{{ asset('images/'.$estatusIcono) }}" alt="{{$estatus}}" class="estado-icono"><span class="{{$estatusClase}}">{{$estatus}}</span></div></td>
                                                            <td width="15%" class="acciones">
                                                                <img src="{{ asset('images/icons/eye-solid-full.svg') }}" alt="Ver Abonos" class="icono icon-toggle" title="Ver Abonos">
                                                                @if($billing->archivo_path)<a href="{{ Storage::url($billing->archivo_path) }}" target="_blank" title="Ver Archivo"> <img src="{{ asset('images/pdf.png') }}" alt="Ver Archivo" class="icono"> </a>@endif
                                                                @if($billing->xml_path)<a href="{{ Storage::url($billing->xml_path) }}" target="_blank" title="Descargar XML">  <img src="{{ asset('images/xml.png') }}" alt="Ver XML" class="icono"> </a>@endif
                                                                <form action="{{ route('Facturacion.destroy', $billing->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar factura {{ $billing->factura_uid }}?');"> @csrf @method('DELETE') <button type="submit" style="background:none; border:none; padding:0; cursor:pointer;" title="Eliminar Factura"> <img src="{{ asset('images/eliminar.png') }}" alt="Eliminar" class="icono"> </button> </form>
                                                            </td>
                                                        </tr>
                                                        <tr class="payment-details-row">
                                                            <td colspan="6" class="payment-details-cell">
                                                                {{-- ... (tu lógica de historial de abonos) ... --}}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            @empty
                                                {{-- Este mensaje ahora se mostrará si el filtro no devuelve nada --}}
                                                <p style="text-align: center; padding: 15px; color: #777;">No hay facturas que coincidan con los filtros seleccionados.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                                </div>
                            </div>
                        </details>
                    @endif
                @empty
                     <p style="text-align: center; color: #888; padding: 20px;">No hay períodos para mostrar. Ejecuta el Seeder.</p>
                @endforelse
            </div>

        @elseif(isset($billings) && isset($periods)) 
            {{-- ========================================================== --}}
            {{-- VISTA PARA ALUMNOS (NUEVO ACORDEÓN) --}}
            {{-- ========================================================== --}}
            <h2>Mis Facturas por Período</h2>
            <div class="period-accordion">
                
                {{-- Iteramos sobre TODOS los períodos disponibles --}}
                @forelse ($periods as $period)
                    @php
                        // De la colección '$billings' (que ya fue filtrada por el controlador),
                        // seleccionamos solo las que pertenecen a ESTE período del bucle.
                        $billingsForPeriod = $billings->where('period_id', $period->id);
                    @endphp
                    
                    {{-- Solo mostramos el período si tiene facturas (después del filtro) --}}
                    @if($billingsForPeriod->isNotEmpty())
                    
                        {{-- Abrimos el período si fue el que se filtró, o si es el primero y no hay filtro --}}
                        <details id="period-{{ $period->id }}" @if(request('period_id') == $period->id || ($loop->first && !request()->filled('period_id'))) open @endif>
                            <summary>
                                <span class="period-name">{{ $period->name }}</span>
                                <span class="period-info">{{ $billingsForPeriod->count() }} factura(s)</span>
                                <span class="arrow-icon">▼</span>
                            </summary>
                            <div class="period-details" style="padding: 0;">
                                
                                {{-- Reutilizamos la tabla que ya tenías --}}
                                <div class="table-container" style="box-shadow: none; border-radius: 0;">
                                    <table style="margin-bottom: 0;">
                                        <thead> <tr> <th>No. Factura</th> <th>Concepto</th> <th>Monto</th> <th>Fecha de Vencimiento</th> <th>Status</th> <th>Acciones</th> </tr> </thead>
                                    </table>

                                    {{-- Iteramos solo sobre las facturas de este período --}}
                                    @foreach ($billingsForPeriod as $billing)
                                        {{-- LÓGICA DE ESTATUS CORREGIDA --}}
                                        @php
                                            $estatus = $billing->status;
                                            $estatusClase = strtolower($estatus);
                                            $totalPagado = $billing->payments->sum('monto');
                                            $saldo = $billing->monto - $totalPagado;

                                            if ($estatus == 'Pendiente') {
                                                if ($totalPagado >= $billing->monto) { $estatus = 'Pagada'; $estatusClase = 'pagada'; } 
                                                elseif ($totalPagado > 0) { $estatus = 'Abonado'; $estatusClase = 'abonado'; }
                                            }

                                            $estatusIcono = 'circulo_rojo.png';
                                            if ($estatus == 'Pagada') { $estatusIcono = 'circulo_verde.png'; } 
                                            elseif ($estatus == 'Abonado') { $estatusIcono = 'circulo_amarillo.png'; }
                                        @endphp

                                        <table style="border-top: none;">
                                            <tbody class="billing-item-tbody">
                                                <tr class="billing-main-row">
                                                    <td width="15%">{{ $billing->factura_uid }}</td>
                                                    <td width="25%">{{ $billing->concepto }}</td>
                                                    <td width="15%">
                                                        ${{ number_format($billing->monto, 2) }}
                                                        @if($estatus == 'Abonado')
                                                            <br><small style="color: #e8a800; font-weight: 600;">(Saldo: ${{ number_format($saldo, 2) }})</small>
                                                        @endif
                                                    </td>
                                                    <td width="15%">{{ \Carbon\Carbon::parse($billing->fecha_vencimiento)->format('d/m/Y') }}</td>
                                                    <td width="15%"> <div class="estado"><img src="{{ asset('images/'.$estatusIcono) }}" alt="{{$estatus}}" class="estado-icono"><span class="{{$estatusClase}}">{{$estatus}}</span></div> </td>
                                                    <td width="15%" class="acciones">
                                                        <img src="{{ asset('images/icons/eye-solid-full.svg') }}" alt="Ver Abonos" class="icono icon-toggle" title="Ver Abonos">
                                                        @if($billing->archivo_path)<a href="{{ Storage::url($billing->archivo_path) }}" target="_blank" title="Ver Archivo"> <img src="{{ asset('images/pdf.png') }}" alt="Ver Archivo" class="icono"> </a>@endif
                                                        @if($billing->xml_path)<a href="{{ Storage::url($billing->xml_path) }}" target="_blank" title="Descargar XML">  <img src="{{ asset('images/xml.png') }}" alt="Ver XML" class="icono"> </a>@endif
                                                    </td>
                                                </tr>
                                                <tr class="payment-details-row">
                                                    <td colspan="6" class="payment-details-cell">
                                                         <div class="payment-history" style="width: 100%;">
                                                            <h4>Historial de Abonos</h4>
                                                            @if($billing->payments->isNotEmpty())
                                                                <ul> @foreach($billing->payments as $payment) <li> <span class="payment-date">{{ \Carbon\Carbon::parse($payment->fecha_pago)->format('d/m/Y') }} - {{ $payment->nota ?? 'Abono' }}</span> <span class="payment-amount">- ${{ number_format($payment->monto, 2) }}</span> </li> @endforeach </ul>
                                                            @else
                                                                <p>No se han registrado abonos para esta factura.</p>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endforeach
                                </div>
                            </div>
                        </details>
                    @endif
                @empty
                     <p style="text-align: center; color: #888; padding: 20px;">No hay períodos para mostrar.</p>
                @endforelse
            </div>
        
        @else
            {{-- Mensaje final si $billings y $usersWithBillings no existen --}}
            <p style="text-align: center; color: #888; padding: 20px;">No hay datos de facturación disponibles.</p>
        @endif
    </div>
    {{-- --- FIN DEL CONTENEDOR CON SCROLL --- --}}

@endsection