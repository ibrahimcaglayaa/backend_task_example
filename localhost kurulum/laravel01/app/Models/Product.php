<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'device';

    protected $fillable = [
        'uid',
        'app_id',
        'language',
        'os',
    ];


    public function productDevice()
    {
        return $this->hasMany(ProductDevice::class, 'client_token', 'client_token');
    }
}
