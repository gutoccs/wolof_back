<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'commerce_id',
        'merchant_id',
        'employee_id',
        'title',
        'description',
        'status',
        'quantity_available',
        'price',
        'sales',
        'original_image',
        'thumbnail_image',
        'avatar_image',
        'real_price',
        'type',
        'visitor_counter',
    ];

    /* Comercio al que pertenece */
    public function commerce()
    {
        return $this->belongsTo('App\Models\Commerce');
    }


    /* Comerciante que creó el producto */
    public function merchant()
    {
        return $this->belongsTo('App\Models\Merchant');
    }


    /* Empleado que montó el producto */
    public function employee()
    {
        return $this->belongsTo('App\Models\Employee');
    }

    // Compras realizadas con este producto
    public function purchases()
    {
        return $this->hasMany('App\Models\Purchase');
    }


}
