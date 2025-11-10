<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'billing_id',
        'user_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'nota',
    ];

    /**
     * Un pago pertenece a una factura.
     */
    public function billing()
    {
        return $this->belongsTo(\App\Models\Facturacion\Billing::class);
    }

    /**
     * Un pago fue registrado por un usuario.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\Users\User::class);
    }
}