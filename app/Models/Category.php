<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'seo_teg',
        'total_image',
        'total_video',
        'total_image',
        'category_star_rate',
        'sorting_position',
    ];

    protected $casts = [
        'seo_teg' => 'array',
         'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
