<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // It's good practice to import the User model

class Billing extends Model
{
    use HasFactory;

    protected $table = 'billings';

    protected $fillable = [
        'factura_uid',
        'user_id',
        'period_id',
        'concepto',
        'monto',
        'fecha_vencimiento',
        'archivo_path',
        'status',
        'xml_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    protected static function newFactory()
    {
    return \Database\Factories\BillingFactory::new();
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Facturacion\Payment::class);
    }

    
}
