<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merchant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['user_id', 'name', 'surname', 'id_public', 'commerce_id'];

    /* Usuario al que pertenece */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get the commerce that owns the Merchant
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function commerce()
    {
        return $this->belongsTo('App\Models\Commerce');
    }

}
