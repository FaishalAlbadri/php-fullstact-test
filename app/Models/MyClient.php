<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MyClient extends Model
{
    use SoftDeletes;

    protected $table = 'my_client';

    protected $fillable = [
        'name', 'slug', 'is_project', 'self_capture',
        'client_prefix', 'client_logo', 'address',
        'phone_number', 'city'
    ];

    protected $dates = ['deleted_at'];
}
