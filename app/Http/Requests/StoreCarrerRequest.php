<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarrerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'official_id'   => 'required|string',
            'name'          => 'required|string|max:255',
            'description1'  => 'required|string',
            'description2'  => 'required|string',
            'description3'  => 'required|string',
            'type'          => 'required|in:Presencial,En linea', // Solo permite uno de estos dos valores
            'semestres'     => 'required|integer|min:1|max:15'  // Debe ser un número entero entre 1 y 15
        ];
    }
}
