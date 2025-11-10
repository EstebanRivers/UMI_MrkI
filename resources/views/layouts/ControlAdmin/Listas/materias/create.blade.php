<div id="createMateriaModal" class="modal-overlay"> 
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="createMateriaModalLabel">Agregar Materia</h5>
            <button type="button" class="close-custom">&times;</button>
        </div>
        
        <div class="modal-body-custom" id="modalBodyContent">
            
            <form method="post" action="{{ route('Listas.materias.store') }}">
                @csrf 

                <div class="form-field lists">
                    <label for="carrera_id">Carrera:</label> 
                    
                    <select id="carrera_id" name="carrera_id" class="@error('carrera_id') validation-error @enderror">
                        <option value="">Seleccione una Carrera</option> 
                        
                        @foreach ($carreras as $carrera)
                            <option 
                                value="{{ $carrera->id }}" {{ old('carrera_id') == $carrera->id ? 'selected' : '' }}>{{ $carrera->name }}</option>
                        @endforeach
                    </select>
                    @error('carrera_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-field">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="@error('nombre') validation-error @enderror" value="{{ old('nombre') }}">
                    @error('nombre')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                
                <div class="form-field lists">
                        <label for="creditos">No. de Creditos:</label>
                        <select id="creditos" name="creditos" class="@error('creditos') validation-error @enderror">
                            @for ($i = 1; $i <= 15; $i++)
                            <option value="{{ $i }}" {{ old('creditos') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        @error('creditos')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                </div>
                
                <div class="options">
                    
                    <div class="form-field Checkboxes">
                        <label>Modalidad:</label>
                        <div>
                            <input type="radio" id="type_presencial" name="type" value="Presencial" {{ old('type') == 'Presencial' ? 'checked' : '' }}>
                            <label for="type_presencial">Presencial:</label>
                        </div>
                        <div>
                            <input type="radio" id="type_enlinea" name="type" value="En linea" {{ old('type') == 'En linea' ? 'checked' : '' }}>
                            <label for="type_enlinea">En linea:</label>
                        </div>
                        @error('type')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-field lists">
                        <label for="semestre">No. de semestres:</label>
                        <select id="semestre" name="semestre" class="@error('semestre') validation-error @enderror">
                            @for ($i = 1; $i <= 15; $i++)
                            <option value="{{ $i }}" {{ old('semestre') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        @error('semestre')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="modal-footer-custom mt-3">
                    <button type="submit" class="submit-button">Agregar</button>
                </div>
            </form>

        </div>
    </div>
</div>