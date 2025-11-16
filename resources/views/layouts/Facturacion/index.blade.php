@extends('layouts.app')

@section('title', 'Facturación')

@section('content')

    <div class="main-header"> <h1>Facturación</h1> </div>

    {{-- Formulario de Filtros --}}
    <form action="{{ route('Facturacion.index') }}" method="GET">
        <div class="filter-controls">
            
            <div class="filter-group">
                <input type="text" name="search" class="search-input" placeholder="Concepto, No. Factura..." value="{{ request('search') }}">
                
                {{-- FILTRO DE STATUS (AHORA VISIBLE PARA TODOS) --}}
                <div class="filter-item">
                    <label for="status">Status (Factura)</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="">TODOS</option>
                        <option value="Pendiente" @selected(request('status') == 'Pendiente')>Pendiente</option>
                        <option value="Abonado" @selected(request('status') == 'Abonado')>Abonado</option>
                        <option value="Pagada" @selected(request('status') == 'Pagada')>Pagada</option>
                    </select>
                </div>

                {{-- FILTRO DE PERÍODO AHORA VISIBLE PARA TODOS --}}
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
                <button typebutton" class="btn export-btn">Exportar</button>
            </div>
        </div>
    </form>


    {{-- Contenido Principal: Depende del Rol --}}
    <div class="scrollable-content-area">

        @if(isset($usersWithBillings)) 
            {{-- ========================================================== --}}
            {{-- VISTA PARA MASTER Y CONTROL ADMIN --}}
            {{-- ========================================================== --}}
            <h2>Usuarios por Período</h2>
            <div class="period-accordion">
                @forelse ($periods as $period)
                    @php
                        // 1. Obtenemos TODOS los usuarios que tienen facturas en este período
                        $usersInThisPeriod = $usersWithBillings->filter(function($user) use ($period) {
                            return $user->billings->where('period_id', $period->id)->isNotEmpty();
                        });

                        // 2. Si el filtro de status está activo, filtramos AHORA los usuarios
                        //    para solo mostrar usuarios que tengan facturas con ESE status.
                        if (request()->filled('status')) {
                            $statusFilter = request('status');
                            $usersInThisPeriod = $usersInThisPeriod->filter(function($user) use ($statusFilter, $period) {
                                // Revisamos si el usuario tiene CUALQUIER factura que coincida
                                return $user->billings->where('period_id', $period->id)->contains(function($billing) use ($statusFilter) {
                                    $estatus = $billing->status;
                                    $totalPagado = $billing->payments->sum('monto');
                                    if ($estatus == 'Pendiente') {
                                        if ($totalPagado >= $billing->monto) { $estatus = 'Pagada'; } 
                                        elseif ($totalPagado > 0) { $estatus = 'Abonado'; }
                                    }
                                    return $estatus === $statusFilter;
                                });
                            });
                        }
                    @endphp
                    
                    @if($usersInThisPeriod->isNotEmpty())
                        <details id="period-{{ $period->id }}" @if(request('period_id') == $period->id || ($loop->first && !request()->filled('period_id'))) open @endif>
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

                                            // 2. FILTRO DE STATUS (para las facturas individuales)
                                            if (request()->filled('status')) {
                                                $statusFilter = request('status');
                                                
                                                $billingsForPeriod = $allBillingsForPeriod->filter(function($billing) use ($statusFilter) {
                                                    $estatus = $billing->status;
                                                    $totalPagado = $billing->payments->sum('monto');
                                                    if ($estatus == 'Pendiente') {
                                                        if ($totalPagado >= $billing->monto) { $estatus = 'Pagada'; } 
                                                        elseif ($totalPagado > 0) { $estatus = 'Abonado'; }
                                                    }
                                                    return $estatus === $statusFilter;
                                                });
                                            } else {
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
                                                    @forelse ($billingsForPeriod as $billing)
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
                                                        
                                                        <table style="margin-bottom: 0; border-top: 1px solid #eee;">
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
                                                                        <div class="clearfix">
                                                                            <div class="payment-history">
                                                                                <h4>Historial de Abonos</h4>
                                                                                @if($billing->payments->isNotEmpty())
                                                                                    <ul> @foreach($billing->payments as $payment) <li> <span class="payment-date">{{ \Carbon\Carbon::parse($payment->fecha_pago)->format('d/m/Y') }} - {{ $payment->nota ?? 'Abono' }}</span> <span class="payment-amount">- ${{ number_format($payment->monto, 2) }}</span> </li> @endforeach </ul>
                                                                                @else
                                                                                    <p>No se han registrado abonos para esta factura.</p>
                                                                                @endif
                                                                            </div>
                                                                            @if($estatus !== 'Pagada')
                                                                            <div class="add-payment-form">
                                                                                <h4>Añadir Abono</h4>
                                                                                <form action="{{ route('payments.store') }}" method="POST">
                                                                                    @csrf
                                                                                    <input type="hidden" name="billing_id" value="{{ $billing->id }}">
                                                                                    <label for="monto_abono_{{ $billing->id }}">Monto a abonar:</label>
                                                                                    <input type="number" id="monto_abono_{{ $billing->id }}" name="monto_abono" step="0.01" max="{{ $saldo }}" placeholder="Máx: ${{ number_format($saldo, 2) }}" required>
                                                                                    <label for="fecha_pago_{{ $billing->id }}">Fecha del pago:</label>
                                                                                    <input type="date" id="fecha_pago_{{ $billing->id }}" name="fecha_pago" value="{{ date('Y-m-d') }}" required>
                                                                                    <label for="nota_abono_{{ $billing->id }}">Nota (Opcional):</label>
                                                                                    <textarea id="nota_abono_{{ $billing->id }}" name="nota_abono" rows="2" placeholder="Ej. Transferencia"></textarea>
                                                                                    <button type="submit" class="guardar-abono">Guardar Abono</button>
                                                                                </form>
                                                                            </div>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    @empty
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
                    <p style="text-align: center; color: #888; padding: 20px;">No hay períodos para mostrar.</p>
                @endforelse
            </div>

        @elseif(isset($billings) && isset($periods)) 
            {{-- ========================================================== --}}
            {{-- VISTA PARA ALUMNOS (NUEVO ACORDEÓN Y FILTRO CORREGIDO) --}}
            {{-- ========================================================== --}}
            <h2>Mis Facturas por Período</h2>
            <div class="period-accordion">
                
                @forelse ($periods as $period)
                    @php
                        // 1. Obtenemos todas las facturas del alumno para ESTE período
                        $allBillingsForPeriod = $billings->where('period_id', $period->id);

                        // 2. ¡AQUÍ ESTÁ EL FILTRO NUEVO!
                        // Si el filtro de status está activo...
                        if (request()->filled('status')) {
                            $statusFilter = request('status');
                            
                            // 3. Filtramos la colección ANTES de mostrarla
                            $billingsForPeriod = $allBillingsForPeriod->filter(function($billing) use ($statusFilter) {
                                
                                // --- Re-calculamos el estatus ---
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
                    
                    {{-- Solo mostramos el período si tiene facturas (después del filtro) --}}
                    @if($billingsForPeriod->isNotEmpty())
                    
                        <details id="period-{{ $period->id }}" @if(request('period_id') == $period->id || ($loop->first && !request()->filled('period_id'))) open @endif>
                            <summary>
                                <span class="period-name">{{ $period->name }}</span>
                                <span class="period-info">{{ $billingsForPeriod->count() }} factura(s)</span>
                                <span class="arrow-icon">▼</span>
                            </summary>
                            <div class="period-details" style="padding: 0;">
                                
                                <div class="table-container" style="box-shadow: none; border-radius: 0;">
                                    <table style="margin-bottom: 0;">
                                        <thead> <tr> <th>No. Factura</th> <th>Concepto</th> <th>Monto</th> <th>Fecha de Vencimiento</th> <th>Status</th> <th>Acciones</th> </tr> </thead>
                                    </table>

                                    {{-- Iteramos solo sobre las facturas de este período --}}
                                    @forelse ($billingsForPeriod as $billing)
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
                                    @empty
                                        {{-- Este mensaje se muestra si el período tenía facturas, pero ninguna coincidió con el filtro --}}
                                        <p style="text-align: center; padding: 15px; color: #777;">No hay facturas que coincidan con los filtros seleccionados en este período.</p>
                                    @endforelse
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


    {{-- ========================================================== --}}
    {{-- INICIO DEL MODAL --}}
    {{-- ESTO DEBERÍA ESTAR EN layouts/app.blade.php PARA QUE FUNCIONE BIEN EL SCROLL --}}
    {{-- ========================================================== --}}
    @if(Auth::check() && (Auth::user()->hasActiveRole('master') || Auth::user()->hasActiveRole('control_administrativo')))
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
    {{-- --- FIN DEL MODAL --- --}}

@endsection