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

    // Definición de constantes para estados (Mejor práctica)
    const STATUS_PENDIENTE = 'Pendiente';
    const STATUS_ABONADO = 'Abonado';
    const STATUS_PAGADA = 'Pagada';

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

    // Relaciones
    public function user() { return $this->belongsTo(User::class); }
    public function period() { return $this->belongsTo(Period::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    
    protected static function newFactory() { return \Database\Factories\BillingFactory::new(); }

    /**
     * Accesor para calcular el estado real (Pendiente, Abonado, Pagada)
     * * FIX CLAVE: Normalizamos el valor $this->status a 'Pendiente', 'Pagada', etc. 
     * antes de compararlo, resolviendo el error de case-sensitivity de la BD.
     */
    public function getComputedStatusAttribute()
    {
        // Normalizamos: la primera letra mayúscula, el resto minúsculas
        $normalizedStatus = ucfirst(strtolower($this->status)); 
        
        $totalPagado = $this->payments->sum('monto');

        if ($normalizedStatus === self::STATUS_PENDIENTE) {
            if (round($totalPagado, 2) >= round($this->monto, 2)) {
                return self::STATUS_PAGADA;
            }
            if ($totalPagado > 0) {
                return self::STATUS_ABONADO;
            }
        }
        
        // Si el estado no era 'Pendiente' o es un estado válido, devolvemos el estado normalizado.
        return $normalizedStatus;
    }
}