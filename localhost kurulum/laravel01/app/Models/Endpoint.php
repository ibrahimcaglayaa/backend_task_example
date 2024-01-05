<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Endpoint extends Model
{
    protected $table = 'endpoint';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'url',
    ];

}
