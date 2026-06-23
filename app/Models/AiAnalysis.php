<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAnalysis extends Model
{
    protected $guarded = [];

    protected $casts = [
        'recommendations' => 'array',
        'content_plan' => 'array',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
