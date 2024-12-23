<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'aliases',
        'image',
        'gender',
        'birth_date',
        'place_of_birth',
        'description',
        'like',
        'dislike',
        'ranking',
        'total_image',
        'total_video',
        'seo_teg',
        'sorting_position',
    ];
    protected $casts = [
        'seo_teg' => 'array', // Cast `seo_teg` as an array
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
