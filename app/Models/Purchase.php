<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'client_id',
        'commerce_id',
        'amount',
        'status',
        'total_to_pay',
        'client_completed',
        'commerce_completed',
        'who_canceled',
        'reason_for_cancellation',
        'merchant_id',
        'employee_id',
        'cancelled_at'
    ];


    // producto a la que pertenece esta compra
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    // Cliente que realizó la compra
    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    // Cliente que realizó la compra
    public function commerce()
    {
        return $this->belongsTo('App\Models\Commerce');
    }


    // Comerciante que canceló la compra (Solo si aplica)
    public function merchant()
    {
        return $this->belongsTo('App\Models\Merchant');
    }

    // Empleado Wolof que canceló la compra (Solo si aplica)
    public function employee()
    {
        return $this->belongsTo('App\Models\Employee');
    }

}
