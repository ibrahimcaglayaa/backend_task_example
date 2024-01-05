<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDevice extends Model
{
    protected $table = 'device_p';
    public $timestamps = false;

    protected $fillable = [
        'client_token',
        'expire_date',
        'hash',
        'status',
        'app',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'client_token', 'client_token');
    }
}
