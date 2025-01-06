<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Banner extends Model
{

    use HasFactory;
    protected $fillable = [
        'name',
        'redirect_url',
        'is_active',
        'image',
        'sorting_position',
    ];
}
