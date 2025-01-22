<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'redirect_url',
        'message',
        'is_active',
        'redirect_url',
        'user_ids',
    ];
    protected $casts = [
        'user_ids' => 'array', 
    ];
}
