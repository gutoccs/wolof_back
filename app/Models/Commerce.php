<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commerce extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'id_public',
        'trade_name',
        'legal_name',
        'tax_identification_number',
        'short_description',
        'slogan',
        'original_profile_image',
        'thumbnail_profile_image',
        'avatar_profile_image',
        'flag_active',
        'observation_flag_active',
    ];

    /**
     * Get all of the merchants for the Commerce
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function merchants()
    {
        return $this->hasMany('App\Models\Merchant');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contact() {
        return $this->hasOne('App\Models\Contact');
    }
}
