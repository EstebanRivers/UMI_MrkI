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
            <div class="action-group">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('Facturacion.index') }}" class="btn btn-secondary">Limpiar</a>
                <button type="button" class="btn export-btn">Exportar</button>
            </div>
        </div>
    </form>

    {{-- === Contenido Principal === --}}
    <div class="scrollable-content-area">

        @if(isset($usersWithBillings)) 
            {{-- ========================================================== --}}
            {{-- VISTA ADMINISTRATIVA (Master/Control Escolar) --}}
            {{-- ========================================================== --}}
            <h2>Usuarios por Período</h2>
            <div class="period-accordion">
                
                @foreach ($periods as $period)
                    @php
                        // Filtro de usuarios y periodo
                        $usersInThisPeriod = $usersWithBillings; 
                        if (request('period_id') && request('period_id') != $period->id) continue;
                    @endphp

                    <details id="period-{{ $period->id }}" @if(request('period_id') == $period->id) open @endif>
                        <summary>
                            <span class="period-name">{{ $period->name }}</span>
                            <span class="period-info">
                                ({{ count($period->meses_calculados) }} Mensualidades)
                            </span>
                            <span class="arrow-icon">▼</span>
                        </summary>
                        
                        <div class="period-details">
                            <div class="user-accordion">
                                @foreach ($usersInThisPeriod as $u)
                                    @php
                                        $userBillings = $u->billings->where('period_id', $period->id);
                                    @endphp

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
                                            {{-- Botón General --}}
                                            <button class="js-trigger-factura" 
                                                data-user-id="{{ $u->id }}" 
                                                data-user-name="{{ $u->nombre }} {{ $u->apellido_paterno }}" 
                                                data-period-id="{{ $period->id }}"
                                                data-period-start="{{ $period->start_date }}"
                                                style="margin-bottom: 20px;">
                                                + Agregar Factura Extra
                                            </button>
                                            
                                          {{-- ITERACIÓN DE MESES ADMIN --}}
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
                                                        @if($facturasDelMes->isEmpty())
                                                            <button class="btn-sm-add btn-open-specific js-trigger-factura"
                                                                data-user-id="{{ $u->id }}"
                                                                data-user-name="{{ $u->nombre }} {{ $u->apellido_paterno }}"
                                                                data-period-id="{{ $period->id }}"
                                                                data-date="{{ $mes['date'] }}"
                                                                data-label="{{ $mes['label'] }}">
                                                                + Agregar
                                                            </button>
                                                        @else
                                                            <span style="font-size: 0.8rem; color: #28a745; font-weight: 600;">✓ Registrada</span>
                                                        @endif
                                                    </div>

                                                    @if($facturasDelMes->isNotEmpty())
                                                        <div class="table-container" style="box-shadow: none; border: none; border-radius: 0;">
                                                            <table style="width:100%; margin: 0;">
                                                                <thead>
                                                                    <tr style="background:#fff; border-bottom:1px solid #eee; color:#777; font-size:12px;">
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
                                                                            $estatus = $billing->status;
                                                                            $totalPagado = $billing->payments->sum('monto');
                                                                            $saldo = $billing->monto - $totalPagado;
                                                                            $estatusClase = strtolower($estatus);
                                                                            if ($estatus == 'Pendiente') {
                                                                                if ($totalPagado >= $billing->monto) { $estatus = 'Pagada'; $estatusClase = 'pagada'; } 
                                                                                elseif ($totalPagado > 0) { $estatus = 'Abonado'; $estatusClase = 'abonado'; }
                                                                            }
                                                                            $estatusIcono = ($estatus == 'Pagada') ? 'circulo_verde.png' : (($estatus == 'Abonado') ? 'circulo_amarillo.png' : 'circulo_rojo.png');
                                                                            $colorStatus = match($estatus) { 'Pagada' => '#28a745', 'Abonado' => '#ffc107', default => '#dc3545' };
                                                                        @endphp
                                                                        
                                                                        {{-- Fila Principal --}}
                                                                        <tr class="billing-main-row">
                                                                            <td style="padding:10px;">{{ $billing->concepto }}</td>
                                                                            <td style="padding:10px;">
                                                                                ${{ number_format($billing->monto, 2) }}
                                                                                @if($estatus == 'Abonado') <br><small style="color:#e8a800">Saldo: ${{number_format($saldo,2)}}</small> @endif
                                                                            </td>
                                                                            <td style="padding:10px;">{{ \Carbon\Carbon::parse($billing->fecha_vencimiento)->format('d/m/Y') }}</td>
                                                                            <td style="padding:10px; font-weight:500;">
                                                                                <div class="estado">
                                                                                    <img src="{{ asset('images/icons/'.$estatusIcono) }}" alt="{{$estatus}}" class="estado-icono" draggable="false">
                                                                                    <span style="color: {{ $colorStatus }}">{{ $estatus }}</span>
                                                                                </div>
                                                                            </td>
                                                                            <td class="acciones" style="padding:10px;">
                                                                                {{-- 1. Icono Ojo --}}
                                                                                <img src="{{ asset('images/icons/eye-solid-full.svg') }}" class="icono icon-toggle" title="Ver Abonos" style="cursor: pointer;" draggable="false">

                                                                                {{-- 2. Icono PDF --}}
                                                                                @if($billing->archivo_path)
                                                                                    <a href="{{ Storage::url($billing->archivo_path) }}" target="_blank" title="Ver Archivo" style="text-decoration: none;">
                                                                                        <img src="{{ asset('images/icons/pdf.png') }}" alt="Ver Archivo" class="icono" style="border:none;" draggable="false">
                                                                                    </a>
                                                                                @endif

                                                                                {{-- 3. Icono XML --}}
                                                                                @if($billing->xml_path)
                                                                                    <a href="{{ Storage::url($billing->xml_path) }}" target="_blank" title="Descargar XML" style="text-decoration: none;">
                                                                                        <img src="{{ asset('images/icons/xml.png') }}" alt="Ver XML" class="icono" style="border:none;" draggable="false">
                                                                                    </a>
                                                                                @endif

                                                                                {{-- 4. Icono Eliminar --}}
                                                                                <form action="{{ route('Facturacion.destroy', $billing->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar factura {{ $billing->factura_uid }}?');">
                                                                                    @csrf @method('DELETE')
                                                                                    <button type="submit" style="background:none; border:none; padding:0; cursor:pointer;" title="Eliminar Factura">
                                                                                        <img src="{{ asset('images/icons/Vector.svg') }}" alt="Eliminar" class="icono" draggable="false"> 
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        
                                                                        {{-- Fila de Detalles de Pagos --}}
                                                                        <tr class="payment-details-row">
                                                                            <td colspan="6" class="payment-details-cell">
                                                                                <div class="clearfix">
                                                                                    <div class="payment-history">
                                                                                        <h4>Historial de Abonos</h4>
                                                                                        @if($billing->payments->isNotEmpty())
                                                                                            <ul>
                                                                                                @foreach($billing->payments as $payment)
                                                                                                    <li>
                                                                                                        <span class="payment-date">
                                                                                                            {{ \Carbon\Carbon::parse($payment->fecha_pago)->format('d/m/Y') }} - {{ $payment->nota ?? 'Abono' }}
                                                                                                        </span>
                                                                                                        <span class="payment-amount">
                                                                                                            - ${{ number_format($payment->monto, 2) }}
                                                                                                        </span>
                                                                                                    </li>
                                                                                                @endforeach
                                                                                            </ul>
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
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div style="padding: 15px; text-align: center; color: #aaa; font-style: italic; font-size: 0.9em;">
                                                            Sin factura registrada para este mes.
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                            {{-- FIN ITERACIÓN MESES ADMIN --}}
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>

        @elseif(isset($billings) && isset($periods)) 
            {{-- ========================================================== --}}
            {{-- CASO 2: VISTA PARA ALUMNOS (ESTUDIANTE) --}}
            {{-- ========================================================== --}}
            <h2>Mis Facturas por Período</h2>
            <div class="period-accordion">
                @foreach ($periods as $period)
                    @php
                        $allBillingsForPeriod = $billings->where('period_id', $period->id);
                        if (request()->filled('status')) {
                            $statusFilter = request('status');
                            $allBillingsForPeriod = $allBillingsForPeriod->filter(function($billing) use ($statusFilter) {
                                $estatus = $billing->status;
                                $totalPagado = $billing->payments->sum('monto');
                                if ($estatus == 'Pendiente') {
                                    if ($totalPagado >= $billing->monto) { $estatus = 'Pagada'; } 
                                    elseif ($totalPagado > 0) { $estatus = 'Abonado'; }
                                }
                                return $estatus === $statusFilter;
                            });
                        }
                        if (request('period_id') && request('period_id') != $period->id) continue;
                    @endphp
                    
                    <details id="period-{{ $period->id }}" @if(request('period_id') == $period->id) open @endif>
                        <summary>
                            <span class="period-name">{{ $period->name }}</span>
                            <span class="period-info">({{ count($period->meses_calculados) }} Mensualidades)</span>
                            <span class="arrow-icon">▼</span>
                        </summary>

                        <div class="period-details">
                            {{-- ITERACIÓN DE MESES ALUMNO --}}
                            <div class="months-container" style="display: flex; flex-direction: column; gap: 15px;">
                                @foreach ($period->meses_calculados as $mes)
                                    @php
                                        $facturasDelMes = $allBillingsForPeriod->filter(function($b) use ($mes) {
                                            return \Carbon\Carbon::parse($b->fecha_vencimiento)->format('Y-m') === $mes['key'];
                                        });
                                    @endphp

                                    <div class="monthly-block" style="border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                                        <div class="month-header" style="background-color: #f8f9fa; padding: 10px 15px; font-weight: 600; color: #223F70; display: flex; justify-content: space-between; align-items: center;">
                                            <span>{{ $mes['label'] }}</span>
                                            @if($facturasDelMes->isNotEmpty())
                                                <span style="font-size: 0.8rem; color: #28a745; font-weight: 600;">✓ Disponible</span>
                                            @else
                                                <span style="font-size: 0.8rem; color: #aaa; font-weight: normal;">-</span>
                                            @endif
                                        </div>

                                        @if($facturasDelMes->isNotEmpty())
                                            <div class="table-container" style="box-shadow: none; border: none; border-radius: 0;">
                                                <table style="width: 100%; margin-bottom: 0;">
                                                    <thead> 
                                                        <tr style="background:#fff; border-bottom:1px solid #eee; color:#777; font-size:12px;"> 
                                                            <th width="15%">Folio</th> 
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
                                                                $estatus = $billing->status;
                                                                $totalPagado = $billing->payments->sum('monto');
                                                                $saldo = $billing->monto - $totalPagado;
                                                                $estatusClase = strtolower($estatus);
                                                                if ($estatus == 'Pendiente') {
                                                                    if ($totalPagado >= $billing->monto) { $estatus = 'Pagada'; $estatusClase = 'pagada'; } 
                                                                    elseif ($totalPagado > 0) { $estatus = 'Abonado'; $estatusClase = 'abonado'; }
                                                                }
                                                                $estatusIcono = ($estatus == 'Pagada') ? 'circulo_verde.png' : (($estatus == 'Abonado') ? 'circulo_amarillo.png' : 'circulo_rojo.png');
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
                                                                        <img src="{{ asset('images/icons/'.$estatusIcono) }}" alt="{{$estatus}}" class="estado-icono" draggable="false">
                                                                        <span style="color: {{ $colorStatus }}">{{ $estatus }}</span>
                                                                    </div>
                                                                </td>
                                                                <td class="acciones" style="padding:10px;">
                                                                    <img src="{{ asset('images/icons/eye-solid-full.svg') }}" class="icono icon-toggle" title="Ver Abonos" draggable="false">
                                                                    @if($billing->archivo_path)<a href="{{ Storage::url($billing->archivo_path) }}" target="_blank"><img src="{{ asset('images/icons/pdf.png') }}" class="icono" draggable="false"></a>@endif
                                                                    @if($billing->xml_path)<a href="{{ Storage::url($billing->xml_path) }}" target="_blank"><img src="{{ asset('images/icons/xml.png') }}" class="icono" draggable="false"></a>@endif
                                                                </td>
                                                            </tr>
                                                            
                                                            {{-- FILA DE PAGOS ALUMNO (Solo Historial - 100% Ancho) --}}
                                                            <tr class="payment-details-row" style="display:none; background:#f9f9f9;">
                                                                <td colspan="6" style="padding: 20px;">
                                                                    <div class="clearfix" style="display: flex; justify-content: space-between;">
                                                                        <div class="payment-history" style="width: 100%;">
                                                                            <h4 style="margin-top:0; margin-bottom:10px; color:#223F70; border-bottom:1px solid #eee; padding-bottom:5px;">Historial de Abonos</h4>
                                                                            @if($billing->payments->isNotEmpty())
                                                                                <ul style="list-style:none; padding:0; margin:0;"> 
                                                                                    @foreach($billing->payments as $payment) 
                                                                                        <li style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px dashed #eee; font-size:13px;">
                                                                                            <span style="color:#666;">
                                                                                                {{ \Carbon\Carbon::parse($payment->fecha_pago)->format('d/m/Y') }} - {{ $payment->nota ?? 'Abono' }}
                                                                                            </span> 
                                                                                            <span style="font-weight:600; color:#e8a800;">
                                                                                                - ${{ number_format($payment->monto, 2) }}
                                                                                            </span>
                                                                                        </li> 
                                                                                    @endforeach 
                                                                                </ul>
                                                                            @else
                                                                                <p style="color:#777; font-style:italic; font-size:13px;">No hay pagos registrados.</p>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>

                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div style="padding: 15px; text-align: center; color: #aaa; font-style: italic; font-size: 0.9em;">
                                                Sin factura registrada para este mes.
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
    
    {{-- FIX: Agregamos style="display: none;" para que no parpadee al cargar --}}
    <div id="modalFactura" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Agregar Factura</h2>
            
            <form id="formFacturaModal" method="POST" action="{{ route('Facturacion.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="modal_user_id" name="user_id" value="">

                {{-- 1. Período Activo --}}
                <label for="modal_period_id">Período Activo:</label>
                <select id="modal_period_id" name="period_id" required class="filter-select" style="width:100%; background-color: #e9ecef; pointer-events: none;" readonly tabindex="-1">
                    @foreach ($periods as $period)
                        @if($period->is_active == 1)
                            <option value="{{ $period->id }}" selected>{{ $period->name }}</option>
                        @endif
                    @endforeach
                </select>

                {{-- 2. Concepto --}}
                <label for="modal_concepto">Concepto:</label>
                <input type="text" id="modal_concepto" name="concepto" required placeholder="Ej. Colegiatura Septiembre">

                {{-- 3. Monto --}}
                <label for="modal_monto">Monto:</label>
                <input type="number" id="modal_monto" name="monto" required step="0.01">

                {{-- 4. Fecha Vencimiento (Lógica automática intacta) --}}
                {{-- FIX: Usamos strong en vez de label para evitar error de input hidden --}}
                <strong style="display:block; margin-top: 10px;">Fecha Vencimiento (Asignada por sistema):</strong>
                
                {{-- IMPORTANTE: Se mantiene el ID 'modal_fecha' para que tu JS funcione --}}
                <input type="hidden" id="modal_fecha" name="fecha" required>
                
                {{-- IMPORTANTE: Se mantiene el ID 'texto_fecha_vencimiento' para que tu JS muestre la fecha --}}
                <p id="texto_fecha_vencimiento" style="font-weight: bold; color: #223F70; margin: 5px 0 15px 0; font-size: 1.1em;"></p>

                {{-- 5. Archivo PDF --}}
                {{-- FIX: Agregamos ID al input y for al label --}}
                <label for="modal_archivo_pdf">Archivo (PDF):</label>
                <input type="file" id="modal_archivo_pdf" name="archivo" accept=".pdf" required>

                {{-- 6. Archivo XML --}}
                {{-- FIX: Agregamos for al label (el ID ya existía) --}}
                <label for="modal_archivo_xml">Subir XML (Opcional):</label>
                <input type="file" id="modal_archivo_xml" name="archivo_xml" accept=".xml,text/xml">

                {{-- 7. Estado --}}
                {{-- FIX: Agregamos ID al select y for al label --}}
                <label for="modal_status">Estado:</label>
                <select id="modal_status" name="status" required>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Pagada">Pagada</option>
                </select>

                <button type="submit" class="guardar">Guardar</button>
            </form>
        </div>
    </div>
    @endif
    <div id="billing-alerts-data" 
         data-alerts="{{ json_encode($alertasVencimiento ?? []) }}" 
         style="display: none;">
    </div>
        
@endsection