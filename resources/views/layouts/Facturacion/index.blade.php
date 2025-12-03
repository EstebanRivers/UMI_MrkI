@extends('layouts.app')

@section('title', 'Facturación')

@section('content')

    <div class="main-header"> <h1>Facturación</h1> </div>

    {{-- === Formulario de Filtros === --}}
    <form action="{{ route('Facturacion.index') }}" method="GET">
        <div class="filter-controls">
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Buscar</label>
                    <input type="text" id="search" name="search" class="search-input" placeholder="Nombre, Concepto..." value="{{ request('search') }}">
                </div>
                <div class="filter-item">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="filter-select">
                        <option value="">TODOS</option>
                        <option value="Pendiente" @selected(request('status') == 'Pendiente')>Pendiente</option>
                        <option value="Abonado" @selected(request('status') == 'Abonado')>Abonado</option>
                        <option value="Pagada" @selected(request('status') == 'Pagada')>Pagada</option>
                    </select>
                </div>
                @if(isset($periods))
                <div class="filter-item">
                    <label for="period_id">Período</label>
                    <select id="period_id" name="period_id" class="filter-select">
                        <option value="">Todos</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>{{ $period->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            
            {{-- GRUPO DE ACCIONES --}}
            <div class="action-group">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('Facturacion.index') }}" class="btn btn-secondary">Limpiar</a>
                
                {{-- 
                    SOLO ADMINS VEN ESTE BOTÓN.
                --}}
                @if(Auth::user()->hasActiveRole('master') || Auth::user()->hasActiveRole('control_administrativo'))
                    {{-- CAMBIO AQUÍ: onclick="submitExport()" --}}
                    <button type="button" class="btn export-btn" onclick="submitExport()">Exportar</button>
                @endif
            </div>
        </div>
    </form>

    {{-- === Contenido Principal === --}}
    <div class="scrollable-content-area">
        @if(isset($usersWithBillings)) 
            {{-- VISTA ADMINISTRADOR --}}
            <div class="period-accordion">
                @foreach ($periods as $period)
                    @php
                        $usersInThisPeriod = $usersWithBillings; 
                        if (request('period_id') && request('period_id') != $period->id) continue;
                        if (empty($period->meses_calculados)) continue;
                    @endphp

                    <details id="period-{{ $period->id }}" @if(request('period_id') == $period->id) open @endif>
                        <summary>
                            <span class="period-name">{{ $period->name }}</span>
                            <span class="period-info">({{ count($period->meses_calculados) }} Mensualidades)</span>
                            <span class="arrow-icon">▼</span>
                        </summary>
                        
                        <div class="period-details">
                            <div class="user-accordion">
                                @foreach ($usersInThisPeriod as $u)
                                    @php $userBillings = $u->billings->where('period_id', $period->id); @endphp
                                    @if(request('status') && $userBillings->isEmpty())
                                        @continue
                                    @endif
                                    <details id="factura-target-user-{{ $u->id }}-period-{{ $period->id }}">
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
                                            @if($period->is_active == 1)
                                            <button class="js-trigger-factura btn-primary" 
                                            data-user-id="{{ $u->id }}" 
                                            data-user-name="{{ $u->nombre }} {{ $u->apellido_paterno }}" 
                                            data-period-id="{{ $period->id }}"
                                            data-uid-prefix="EXT-" 
                                            
                                            style="margin-bottom: 20px;">
                                            + Agregar Factura Extra
                                        </button>
                                        @endif
                                        
                                            
                                            {{-- ITERACIÓN DE MESES Y FACTURAS --}}
                                            <div class="months-container" style="display: flex; flex-direction: column; gap: 15px;">
                                                @foreach ($period->meses_calculados as $mes)
                                                    @php
                                                        $facturasDelMes = $userBillings->filter(function($b) use ($mes) {
                                                            return \Carbon\Carbon::parse($b->fecha_vencimiento)->format('Y-m') === $mes['key'];
                                                        });
                                                    @endphp

                                                    <div class="monthly-block" style="border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                                                        <div class="month-header" style="background: #f8f9fa; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center;">
                                                            <strong style="color: #223F70;">{{ $mes['label'] }}</strong>
                                                            @if($period->is_active == 1)
                                                            @if($facturasDelMes->isEmpty())
                                                                <button class="btn-sm-add btn-open-specific js-trigger-factura"
                                                                data-user-id="{{ $u->id }}"
                                                                data-user-name="{{ $u->nombre }} {{ $u->apellido_paterno }}"
                                                                data-period-id="{{ $period->id }}"
                                                                data-uid-prefix="MEN-" 
                                                                
                                                                data-date="{{ $mes['date'] }}"
                                                                data-label="{{ $mes['label'] }}">
                                                                + Agregar
                                                            </button>
                                                            @endif
                                                            @else
                                                                <span style="font-size: 0.8rem; color: #28a745; font-weight: 600;">✓ Registrada</span>
                                                            @endif
                                                        </div>

                                                        @if($facturasDelMes->isNotEmpty())
                                                            <div class="table-container" style="box-shadow: none; border: none; border-radius: 0;">
                                                                <table style="width:100%; margin: 0;">
                                                                    <thead>
                                                                        <tr style="background:#fff; border-bottom:1px solid #eee; color:#777; font-size:12px;">
                                                                            <th width="15%">ID</th>
                                                                            <th width="20%">Concepto</th>
                                                                            <th width="15%">Monto</th>
                                                                            <th width="15%">Vence</th>
                                                                            <th width="15%">Estado</th>
                                                                            <th width="20%">Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="billing-item-tbody">
                                                                        @foreach($facturasDelMes as $billing)
                                                                            @php
                                                                                $estatus = $billing->computed_status;
                                                                                $totalPagado = $billing->payments->sum('monto');
                                                                                $saldo = $billing->monto - $totalPagado;
                                                                                $estatusIcono = match($estatus) { 'Pagada' => 'circulo_verde.png', 'Abonado' => 'circulo_amarillo.png', default => 'circulo_rojo.png' };
                                                                                $colorStatus = match($estatus) { 'Pagada' => '#28a745', 'Abonado' => '#ffc107', default => '#dc3545' };
                                                                            @endphp
                                                                            <tr class="billing-main-row">
                                                                                <td style="padding:10px;">{{ $billing->factura_uid }}</td>
                                                                                <td style="padding:10px;">{{ $billing->concepto }}</td>
                                                                                <td style="padding:10px;">
                                                                                    ${{ number_format($billing->monto, 2) }}
                                                                                    @if($estatus == 'Abonado') <br><small style="color:#e8a800">Saldo: ${{number_format($saldo,2)}}</small> @endif
                                                                                </td>
                                                                                <td style="padding:10px;">{{ \Carbon\Carbon::parse($billing->fecha_vencimiento)->format('d/m/Y') }}</td>
                                                                                <td style="padding:10px; font-weight:500;">
                                                                                    <div class="estado">
                                                                                        <img src="{{ asset('images/icons/'.$estatusIcono) }}" alt="{{$estatus}}" class="estado-icono" draggable="false" oncontextmenu="return false;">
                                                                                        <span style="color: {{ $colorStatus }}">{{ $estatus }}</span>
                                                                                    </div>
                                                                                </td>
                                                                                <td class="acciones" style="padding:10px;">
                                                                                    <img src="{{ asset('images/icons/eye-solid-full.svg') }}" class="icono icon-toggle" title="Ver Abonos" style="cursor: pointer;" draggable="false">
                                                                                    @if($billing->archivo_path)<a href="{{ Storage::url($billing->archivo_path) }}" target="_blank"><img src="{{ asset('images/icons/pdf.png') }}" class="icono" draggable="false"></a>@endif
                                                                                    @if($billing->xml_path)<a href="{{ Storage::url($billing->xml_path) }}" target="_blank"><img src="{{ asset('images/icons/xml.png') }}" class="icono" draggable="false"></a>@endif
                                                                                    
                                                                                   <form action="{{ route('Facturacion.destroy', $billing->id) }}" 
                                                                                    method="POST" 
                                                                                    class="form-eliminar" 
                                                                                    data-uid="{{ $billing->factura_uid }}" {{-- Pasamos el folio aquí --}}
                                                                                    style="display:inline;">
                                                                                    @csrf 
                                                                                    @method('DELETE')
                                                                                    <button type="submit" style="background:none; border:none; padding:0; cursor:pointer;" title="Eliminar">
                                                                                    <img src="{{ asset('images/icons/Vector.svg') }}" class="icono" draggable="false">
                                                                                    </button>
                                                                                    </form>
                                                                                </td>
                                                                            </tr>
                                                                            {{-- Fila Detalles de Pagos --}}
                                                                            <tr class="payment-details-row" style="display:none;">
                                                                                <td colspan="6" class="payment-details-cell">
                                                                                    <div class="payment-history"><h4>Historial</h4>
                                                                                        @if($billing->payments->isNotEmpty())
                                                                                            <ul> @foreach($billing->payments as $payment) <li> <span class="payment-date">{{ \Carbon\Carbon::parse($payment->fecha_pago)->format('d/m/Y') }} - {{ $payment->nota ?? 'Abono' }}</span> <span class="payment-amount">- ${{ number_format($payment->monto, 2) }}</span> </li> @endforeach </ul>
                                                                                        @else <p>Sin abonos</p> @endif
                                                                                    </div>
                                                                                    @if($estatus !== 'Pagada')
                                                                                        <div class="add-payment-form">
                                                                                            <h4>Añadir Abono</h4>
                                                                                            <form action="{{ route('payments.store') }}" method="POST">
                                                                                                @csrf <input type="hidden" name="billing_id" value="{{ $billing->id }}">
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
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>

        @elseif(isset($billings) && isset($periods)) 
            {{-- VISTA ALUMNO --}}

            <div class="period-accordion">
                 @foreach ($periods as $period)
                    @php
                        $allBillingsForPeriod = $billings->where('period_id', $period->id);
                        if (request('period_id') && request('period_id') != $period->id) continue;
                    @endphp
                    
                    <details id="period-{{ $period->id }}" @if(request('period_id') == $period->id) open @endif>
                        {{-- CAMBIO: Summary color Azul y texto blanco --}}
                        <summary style="background-color: #223F70; color: #fff;">
                            <span class="period-name" style="color: #fff;">{{ $period->name }}</span>
                            <span class="period-info" style="color: #e9ecef;">({{ count($period->meses_calculados) }} Mensualidades)</span>
                            <span class="arrow-icon" style="color: #fff;">▼</span>
                        </summary>
                        
                        <div class="period-details">
                             <div class="months-container" style="display: flex; flex-direction: column; gap: 15px;">
                                @foreach ($period->meses_calculados as $mes)
                                    @php
                                        $facturasDelMes = $allBillingsForPeriod->filter(function($b) use ($mes) {
                                            return \Carbon\Carbon::parse($b->fecha_vencimiento)->format('Y-m') === $mes['key'];
                                        });
                                    @endphp
                                    <div class="monthly-block" style="border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                                        
                                        {{-- CAMBIO: Encabezado del mes color Azul y texto blanco --}}
                                        <div class="month-header" style="background-color: #ffffffff; padding: 10px 15px; font-weight: 600; color: #223F70; display: flex; justify-content: space-between; align-items: center;">
                                            <span>{{ $mes['label'] }}</span>
                                            @if($facturasDelMes->isNotEmpty())
                                                <span style="font-size: 0.8rem; color: #223F70; font-weight: 600; background: #fff; padding: 2px 8px; border-radius: 10px;">✓ Disponible</span>
                                            @else
                                                <span style="font-size: 0.8rem; color: #ccc; font-weight: normal;">-</span>
                                            @endif
                                        </div>
                                        
                                        @if($facturasDelMes->isNotEmpty())
                                            {{-- TABLA DE FACTURAS DEL ALUMNO --}}
                                            <div class="table-container" style="box-shadow: none; border: none; border-radius: 0;">
                                                <table style="width: 100%; margin-bottom: 0;">
                                                    <thead> 
                                                        {{-- CAMBIO: Cabecera de tabla color Azul y texto blanco --}}
                                                        <tr style="background:#223F70; border-bottom:1px solid #152744; color:#fff; font-size:12px;"> 
                                                            <th width="25%">Concepto</th> 
                                                            <th width="15%">Monto</th> 
                                                            <th width="15%">Vencimiento</th> 
                                                            <th width="15%">Status</th> 
                                                            <th width="15%">Acciones</th> 
                                                        </tr> 
                                                    </thead>
                                                    <tbody class="billing-item-tbody">
                                                        @foreach ($facturasDelMes as $billing)
                                                            @php
                                                                $estatus = $billing->computed_status;
                                                                $totalPagado = $billing->payments->sum('monto');
                                                                $saldo = $billing->monto - $totalPagado;
                                                                $estatusIcono = match($estatus) { 'Pagada' => 'circulo_verde.png', 'Abonado' => 'circulo_amarillo.png', default => 'circulo_rojo.png' };
                                                                $colorStatus = match($estatus) { 'Pagada' => '#28a745', 'Abonado' => '#ffc107', default => '#dc3545' };
                                                            @endphp
                                                            <tr class="billing-main-row">
                                                                <td style="padding:10px;">{{ $billing->concepto }}</td>
                                                                <td style="padding:10px;">
                                                                    ${{ number_format($billing->monto, 2) }}
                                                                    @if($estatus == 'Abonado') <br><small style="color:#e8a800">Saldo: ${{number_format($saldo,2)}}</small> @endif
                                                                </td>
                                                                <td style="padding:10px;">{{ \Carbon\Carbon::parse($billing->fecha_vencimiento)->format('d/m/Y') }}</td>
                                                                <td style="padding:10px; font-weight:500;">
                                                                    <div class="estado">
                                                                        {{-- AQUÍ SE AGREGÓ oncontextmenu="return false;" --}}
                                                                        <img src="{{ asset('images/icons/'.$estatusIcono) }}" alt="{{$estatus}}" class="estado-icono" draggable="false" oncontextmenu="return false;">
                                                                        <span style="color: {{ $colorStatus }}">{{ $estatus }}</span>
                                                                    </div>
                                                                </td>
                                                                <td class="acciones" style="padding:10px;">
                                                                    <img src="{{ asset('images/icons/eye-solid-full.svg') }}" class="icono icon-toggle" title="Ver Historial" oncontextmenu="return false;">
                                                                    @if($billing->archivo_path)<a href="{{ Storage::url($billing->archivo_path) }}" target="_blank"><img src="{{ asset('images/icons/pdf.png') }}" class="icono" draggable="false" oncontextmenu="return false;"></a>@endif
                                                                    @if($billing->xml_path)<a href="{{ Storage::url($billing->xml_path) }}" target="_blank"><img src="{{ asset('images/icons/xml.png') }}" class="icono" draggable="false" oncontextmenu="return false;"></a>@endif
                                                                </td>
                                                            </tr>
                                                            {{-- FILA HISTORIAL ALUMNO (SOLO LECTURA) --}}
                                                            <tr class="payment-details-row" style="display:none; background:#f9f9f9;">
                                                                <td colspan="6" style="padding: 20px;">
                                                                    <div class="payment-history" style="width: 100%;">
                                                                        <h4 style="margin:0 0 10px; color:#223F70;">Historial de Abonos</h4>
                                                                        @if($billing->payments->isNotEmpty())
                                                                        <ul> @foreach($billing->payments as $payment) <li> <span class="payment-date">{{ \Carbon\Carbon::parse($payment->fecha_pago)->format('d/m/Y') }} - {{ $payment->nota ?? 'Abono' }}</span> <span class="payment-amount">- ${{ number_format($payment->monto, 2) }}</span> </li> @endforeach </ul>
                                                                        @else <p>No hay pagos registrados.</p> @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                             </div>
                        </div>
                    </details>
                 @endforeach
            </div>
        @else
            <p style="text-align: center; color: #888; padding: 20px;">No hay datos de facturación disponibles.</p>
        @endif
    </div>
@if(Auth::check() && (Auth::user()->hasActiveRole('master') || Auth::user()->hasActiveRole('control_administrativo')))
    
    <div id="modalFactura" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            
            {{-- TÍTULO OPTIMIZADO: La estructura y el salto de línea (<br>) ya están aquí. 
                 El JS solo rellenará el span #modalUserName. --}}
            <h2 id="modalTitle" style="margin-top: 0; margin-bottom: 20px; color: #223F70; font-size: 26px; font-weight: 600; border-bottom: 1px solid #eee; padding-bottom: 10px; text-align: center; font-family: 'Poppins', sans-serif;">
                Agregar Factura a:<br>
                <span id="modalUserName" style="display:block; margin-top:5px; font-weight:700; font-size: 0.9em;"></span>
            </h2>
            
           <form id="formFacturaModal" method="POST" action="{{ route('Facturacion.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="modal_user_id" name="user_id" value="">
            <input type="hidden" id="modal_uid_prefix" name="uid_prefix" value="EXT-">

                {{-- 1. Período Activo --}}
                <label for="modal_period_id" style="font-weight:bold; display:block; margin-top:10px;">Período Activo:</label>
                <select id="modal_period_id" name="period_id" required class="filter-select" style="width:100%; background-color: #e9ecef; pointer-events: none;" readonly tabindex="-1">
                    @foreach ($periods as $period)
                        @if($period->is_active == 1)
                            <option value="{{ $period->id }}" selected>{{ $period->name }}</option>
                        @endif
                    @endforeach
                </select>

                {{-- 2. Concepto --}}
                <label for="modal_concepto" style="font-weight:bold; display:block; margin-top:10px;">Concepto:</label>
                <select id="modal_concepto" name="concepto" required class="filter-select" style="width: 100%; padding: 8px;">
                    <option value="" data-amount="">   Seleccione un concepto   </option>
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
                <input type="hidden" id="modal_monto" name="monto" required>

                {{-- 4. Fecha Vencimiento --}}
                <strong style="display:block; margin-top: 10px;">Fecha Vencimiento (Asignada por sistema):</strong>
                
                {{-- IMPORTANTE: Se mantiene el ID 'modal_fecha' para que tu JS funcione --}}
                <input type="hidden" id="modal_fecha" name="fecha" required>
                
                {{-- IMPORTANTE: Se mantiene el ID 'texto_fecha_vencimiento' para que tu JS muestre la fecha --}}
                <p id="texto_fecha_vencimiento" style="font-weight: bold; color: #223F70; margin: 5px 0 15px 0; font-size: 1.1em;"></p>

                {{-- 5. Estado --}}
                <label for="modal_status" style="font-weight:bold; display:block; margin-top:10px;">Estado:</label>
                <select id="modal_status" name="status" required style="width: 100%; padding: 8px; margin-bottom: 20px;">
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



                <button type="submit" class="guardar" style="width: 100%; padding: 12px; background-color: #223F70; color: white; border: none; border-radius: 4px; cursor: pointer;">Guardar Factura</button>
            </form>
        </div>
    </div>
@endif

<div id="billing-alerts-data" 
     data-alerts="{{ json_encode($alertasVencimiento ?? []) }}" 
     style="display: none;">
</div>

@endsection