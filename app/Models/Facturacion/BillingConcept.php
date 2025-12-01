<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingConcept extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla en BD
    protected $table = 'billing_concepts'; 

    protected $fillable = [
        'institution_id',
        'concept',
        'amount',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'amount'    => 'decimal:2',
    ];
}