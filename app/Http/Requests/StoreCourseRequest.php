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
            'credits' => $creditsRule,
            'hours' => 'required|integer|min:0',
            'workstation_id' => 'nullable|exists:workstations,id',
            'guide_material' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:40960', // max 20MB
            'cert_bg_image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Máx 2MB
            'cert_sig_1_image' => 'nullable|image|mimes:png|max:1024', // Preferible PNG para firmas transparentes
            'cert_sig_2_image' => 'nullable|image|mimes:png|max:1024',
            'cert_sig_1_name'  => 'nullable|string|max:100',
            'cert_sig_2_name'  => 'nullable|string|max:100',
        ];
    }
}
