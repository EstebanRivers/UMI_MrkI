<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id', 'career_id', 'semestre', 'periodo',
        'doc_acta_nacimiento', 'doc_certificado_prepa',
        'doc_curp', 'doc_ine', 'doc_comprobante_domicilio',
        'status'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function career() {
        return $this->belongsTo(Career::class);
    }
}