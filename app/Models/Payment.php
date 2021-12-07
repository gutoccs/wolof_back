<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'purchase_id',
        'flag_error',
        'id_transaccion',
        'es_real',
        'es_aprobada',
        'codigo_autorizacion',
        'mensaje',
        'forma_pago',
        'monto',
        'servicio_error',
        'mensajes_error'
    ];

    /* Compra al que pertenece el pago*/
    public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase');
    }
}
