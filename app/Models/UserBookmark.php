<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBookmark extends Model
{
    protected $fillable = ['user_id', 'manga_id', 'manga_title', 'cover_url', 'notes', 'is_reading'];

    protected $casts = [
        'is_reading' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
