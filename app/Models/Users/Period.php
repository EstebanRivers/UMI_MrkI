<?php

namespace App\Models\Users; 

use App\Models\Users\Institution; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Facturacion\Billing; // Importante para la relación

class Period extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'institution_id',
        'monthly_payments_count',
        'payment_dates',
    ];

    /**
     * Define los casts para los tipos de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'payment_dates' => 'array',
    ];

    // --- RELACIONES ---

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    // 1. AGREGAR ESTA RELACIÓN (Faltaba)
    public function billings()
    {
        return $this->hasMany(Billing::class, 'period_id');
    }

    // --- EVENTOS DEL MODELO ---

    // 2. AGREGAR ESTE MÉTODO MÁGICO (Borrado en Cascada)
    protected static function boot() {
        parent::boot();

        static::deleting(function($period) {
            // Cuando se borre este periodo, busca todas sus facturas y bórralas también
            $period->billings()->each(function($billing) {
                $billing->delete(); // Esto activará el SoftDelete en la factura
            });
        });
    }
}