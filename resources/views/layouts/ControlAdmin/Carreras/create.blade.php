<div id="createCareerModal" class="modal-overlay"> 
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="createCareerModalLabel">Agregar Carrera</h5>
            <button type="button" id="closeModalBtn" class="close-custom">&times;</button>
        </div>
        
        <div class="modal-body-custom" id="modalBodyContent">
            
            <form method="post" action="{{ route('career.store') }}">
                @csrf 
                
                <div class="form-field">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" class="@error('name') validation-error @enderror" value="{{ old('name') }}">
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-field">
                    <label for="official_id">RVOE:</label>
                    <input type="text" id="official_id" name="official_id" class="@error('official_id') validation-error @enderror" value="{{ old('official_id') }}">
                    @error('official_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-field">
                    <label for="description1">Profesionalización y empleabilidad:</label>
                    <input type="text" id="description1" name="description1" class="@error('description1') validation-error @enderror" value="{{ old('description1') }}">
                    @error('description1')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-field">
                    <label for="description2">Objetivo General:</label>
                    <input type="text" id="description2" name="description2" class="@error('description2') validation-error @enderror" value="{{ old('description2') }}">
                    @error('description2')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-field">
                    <label for="description3">Elige Ser:</label>
                    <input type="text" id="description3" name="description3" class="@error('description3') validation-error @enderror" value="{{ old('description3') }}">
                    @error('description3')
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
                        <label for="semesters">No. de semestres:</label>
                        <select id="semesters" name="semesters" class="@error('semesters') validation-error @enderror">
                            @for ($i = 1; $i <= 15; $i++)
                            <option value="{{ $i }}" {{ old('semesters') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        @error('semesters')
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