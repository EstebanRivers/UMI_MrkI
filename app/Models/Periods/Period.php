<?php

namespace App\Models\Periods;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    protected $table = 'periods';
    protected $fillable = [ /* ... */ ];

    protected static function newFactory()
    {
        // Apunta a la fábrica DENTRO de la subcarpeta 'Periods'
        return \Database\Factories\PeriodFactory::new();
    }

    /**
     * Un período puede tener muchas facturas.
     */
    public function billings()
    {
        return $this->hasMany(\App\Models\Facturacion\Billing::class);
    }
}