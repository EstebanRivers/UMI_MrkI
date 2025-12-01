<div id="editCareerModal_{{ $career->id }}" class="modal-overlay"> 
    <div class="modal-content-container">
        <div class="modal-header-custom">
            <h5 id="editCareerModalLabel">Editar Carrera: {{ $career->name }}</h5>
            <button type="button" id="closeEditModalBtn_{{ $career->id }}" class="close-custom">&times;</button>
        </div>
        
        <div class="modal-body-custom" id="modalBodyContentEdit">
            
            <form method="post" action="{{ route('control.careers.update', $career->id) }}">
                @csrf 
                @method('PUT') 
                
                {{-- 1. Nombre --}}
                <div class="form-field">
                    <label for="name_{{ $career->id }}">Nombre:</label>
                    <input type="text" id="name_{{ $career->id }}" name="name" 
                           value="{{ old('name', $career->name) }}">
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- 2. RVOE --}}
                <div class="form-field">
                    <label for="official_id_{{ $career->id }}">RVOE:</label>
                    <input type="text" id="official_id_{{ $career->id }}" name="official_id" 
                           value="{{ old('official_id', $career->official_id) }}">
                    @error('official_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 3. Desc1 --}}
                <div class="form-field">
                    <label for="description1_{{ $career->id }}">Profesionalización y empleabilidad:</label>
                    <input type="text" id="description1_{{ $career->id }}" name="description1" 
                           value="{{ old('description1', $career->description1) }}">
                    @error('description1')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 4. Desc2 --}}
                <div class="form-field">
                    <label for="description2_{{ $career->id }}">Objetivo General:</label>
                    <input type="text" id="description2_{{ $career->id }}" name="description2" 
                           value="{{ old('description2', $career->description2) }}">
                    @error('description2')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 5. Desc3 --}}
                <div class="form-field">
                    <label for="description3_{{ $career->id }}">Elige Ser:</label>
                    <input type="text" id="description3_{{ $career->id }}" name="description3" 
                           value="{{ old('description3', $career->description3) }}">
                    @error('description3')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="options">
                    
                    {{-- 6. Modalidad (Radios) --}}
                    <div class="Checkboxes form-field">
                        <label>Modalidad:</label>
                        @php $currentType = old('type', $career->type); @endphp
                        <div>
                            <input type="radio" id="type_presencial_{{ $career->id }}" name="type" value="Presencial" 
                                   {{ $currentType == 'Presencial' ? 'checked' : '' }}>
                            <label for="type_presencial_{{ $career->id }}">Presencial:</label>
                        </div>
                        <div>
                            <input type="radio" id="type_enlinea_{{ $career->id }}" name="type" value="En linea" 
                                   {{ $currentType == 'En linea' ? 'checked' : '' }}>
                            <label for="type_enlinea_{{ $career->id }}">En linea:</label>
                        </div>
                        @error('type')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 7. Semestres (select) --}}
                    <div class="lists form-field">
                        <label for="semesters_{{ $career->id }}">No. de semestres:</label>
                        <select id="semesters_{{ $career->id }}" name="semesters">
                            @php $currentSemesters = old('semesters', $career->semesters); @endphp
                            @for ($i = 1; $i <= 15; $i++)
                            <option value="{{ $i }}" {{ $currentSemesters == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        @error('semesters')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="modal-footer-custom mt-3">
                    <button type="submit" class="submit-button">Actualizar</button>
                </div>
            </form>

        </div>
    </div>
</div>