<div id="editMateriaModal_{{ $registro->id }}" class="modal-overlay"> 
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="editMateriaModalLabel">Editar Materia: {{ $registro->nombre }}</h5>
            <button type="button" class="close-custom">&times;</button>
        </div>
        
        <div class="modal-body-custom" id="modalBodyContent">
            
            <form method="post" action="{{ route('Listas.materias.update', $registro) }}">
                @csrf 
                @method('PUT') {{-- NECESARIO para que Laravel lo trate como una actualización --}}

                {{-- Campo: Carrera (carrera_id) --}}
                <div class="form-field lists">
                    <label for="carrera_id">Carrera:</label> 
                    <select id="carrera_id" name="carrera_id" class="@error('carrera_id') validation-error @enderror">
                        <option value="">Seleccione una Carrera</option> 
                        
                        {{-- La clave foránea en la DB es 'career_id' --}}
                        @foreach ($carreras as $carrera) 
                            <option 
                                value="{{ $carrera->id }}" 
                                {{-- Usamos el valor actual de la materia si no hay old input --}}
                                {{ old('carrera_id', $registro->career_id) == $carrera->id ? 'selected' : '' }}
                            >
                                {{ $carrera->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('carrera_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- Campo: Nombre --}}
                <div class="form-field">
                    <label for="nombre">Nombre:</label>
                    {{-- El valor inicial es el de la materia --}}
                    <input type="text" id="nombre" name="nombre" class="@error('nombre') validation-error @enderror" value="{{ old('nombre', $registro->nombre) }}">
                    @error('nombre')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- Campo: No. Créditos --}}
                <div class="form-field lists">
                    <label for="creditos">No. de Creditos:</label>
                    <select id="creditos" name="creditos" class="@error('creditos') validation-error @enderror">
                        @for ($i = 1; $i <= 15; $i++)
                            <option 
                                value="{{ $i }}" 
                                {{ old('creditos', $registro->creditos) == $i ? 'selected' : '' }}
                            >{{ $i }}</option>
                        @endfor
                    </select>
                    @error('creditos')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="options">
                    
                    {{-- Campo: Modalidad (type) --}}
                    <div class="form-field Checkboxes">
                        <label>Modalidad:</label>
                        <div>
                            <input type="radio" id="type_presencial_{{ $registro->id }}" name="type" value="Presencial" {{ old('type', $registro->type) == 'Presencial' ? 'checked' : '' }}>
                            <label for="type_presencial_{{ $registro->id }}">Presencial:</label>
                        </div>
                        <div>
                            <input type="radio" id="type_enlinea_{{ $registro->id }}" name="type" value="En linea" {{ old('type', $registro->type) == 'En linea' ? 'checked' : '' }}>
                            <label for="type_enlinea_{{ $registro->id }}">En linea:</label>
                        </div>
                        @error('type')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    {{-- Campo: Semestre --}}
                    <div class="form-field lists">
                        <label for="semestre">No. de Semestre:</label>
                        <select id="semestre" name="semestre" class="@error('semestre') validation-error @enderror">
                            @for ($i = 1; $i <= 15; $i++)
                                <option 
                                    value="{{ $i }}" 
                                    {{ old('semestre', $registro->semestre) == $i ? 'selected' : '' }}
                                >{{ $i }}</option>
                            @endfor
                        </select>
                        @error('semestre')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        <input type="hidden" name="clave" value="{{ old('clave', $registro->clave) }}">
                        <input type="hidden" name="descripcion" value="{{ old('descripcion', $registro->descripcion) }}">
                    </div>
                </div>              
                <div class="modal-footer-custom mt-3">
                    <button type="button" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="submit-button">Guardar Cambios</button>
                </div>
            </form>

        </div>
    </div>
</div>