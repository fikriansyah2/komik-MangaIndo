<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReadingProgress extends Model
{
    protected $fillable = [
        'user_id', 'manga_id', 'chapter_id', 'chapter_number',
        'page', 'total_pages', 'manga_title', 'chapter_title', 'cover_url'
    ];

    protected $casts = [
        'page' => 'integer',
        'total_pages' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_pages === 0) {
            return 0;
        }
        return round(($this->page / $this->total_pages) * 100);
    }
}
