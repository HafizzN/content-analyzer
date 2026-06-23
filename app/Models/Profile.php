<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'analyzed_at' => 'datetime',
    ];

    public function videos()
    {
        return $this->hasMany(Video::class)->orderBy('post_date', 'desc');
    }

    public function aiAnalysis()
    {
        return $this->hasOne(AiAnalysis::class);
    }
}
