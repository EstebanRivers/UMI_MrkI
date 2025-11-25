<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Users\User;
use App\Models\Users\Period; 

class Billing extends Model
{
    use HasFactory, SoftDeletes; 

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

    protected $dates = ['deleted_at']; 

    public function user() { return $this->belongsTo(User::class); }
    public function period() { return $this->belongsTo(Period::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    protected static function newFactory() { return \Database\Factories\BillingFactory::new(); }
}