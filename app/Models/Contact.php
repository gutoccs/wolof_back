<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'commerce_id',
        'web',
        'whatsapp',
        'instagram',
        'facebook',
        'twitter',
        'linkedin',
        'youtube',
        'tiktok',
        'phone_1',
        'phone_2',
        'email',
    ];


    public function commerce()
    {
        return $this->belongsTo('App\Models\Commerce');
    }

}
