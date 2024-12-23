<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subscriber',
        'description',
        'image',
        'seo_teg',
        'like',
        'dislike',
        'ratting',
        'sorting_position',
        'total_image',
        'total_video',
        'total_view',
    ];

    protected $casts = [
        'seo_teg' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
