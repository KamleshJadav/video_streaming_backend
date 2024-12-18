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
        'total_video',
        'category_star_rate',
        'sorting_postion',
    ];

    protected $casts = [
        'seo_teg' => 'array', // Cast `seo_teg` as an array
    ];
}
