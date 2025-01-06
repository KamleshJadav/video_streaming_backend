<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class WishlistVideo extends Model
{
    use HasFactory;
  
    protected $fillable = [
        'user_id',
        'video_id',
        'added_at',
    ];
  
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
