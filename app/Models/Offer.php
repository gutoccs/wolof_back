<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
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
        'price',
        'sales',
        'original_image',
        'thumbnail_image',
        'avatar_image',
    ];

    /* Comercio al que pertenece */
    public function commerce()
    {
        return $this->belongsTo('App\Models\Commerce');
    }


    /* Comerciante que creó la oferta */
    public function merchant()
    {
        return $this->belongsTo('App\Models\Merchant');
    }


    /* Empleado que montó la oferta */
    public function employee()
    {
        return $this->belongsTo('App\Models\Employee');
    }


}
