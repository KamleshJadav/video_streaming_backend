<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Channel;
use App\Models\Actor;


class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'video', 
        'actor_id', 
        'category_id', 
        'channel_id', 
        'views', 
        'likes', 
        'description', 
        'seo_teg'
    ];

    protected $casts = [
        'actor_id' => 'array',
        'seo_teg' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
    
 
}
