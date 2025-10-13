<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Users\Institution;

class StoreCourseRequest extends FormRequest
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
        // 1. Get the institution ID DIRECTLY from the session.
        $institutionId = session('active_institution_id');
        $institution = $institutionId ? Institution::find($institutionId) : null;

        // 2. Define the rule for credits based on the session's institution.
        $creditsRule = 'nullable|integer|min:0'; // Default: optional
        if ($institution && $institution->name === 'Universidad Mundo Imperial') {
            $creditsRule = 'required|integer|min:0'; // Required only for UMI
        }

        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'institution_id' => 'required|exists:institutions,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validación de imagen
            'hours' => 'required|integer|min:0',
            'credits' => $creditsRule,
            'career_id' => 'nullable|exists:careers,id',
            'department_id' => 'nullable|exists:departments,id',
            'workstation_id' => 'nullable|exists:workstations,id',
          
        ];
    }
}
