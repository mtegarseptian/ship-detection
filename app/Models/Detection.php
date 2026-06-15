<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detection extends Model
{
    protected $fillable = [
        'image_original', 'image_result', 'model_ai_id',
        'user_id', 'ship_count', 'bounding_boxes', 'status',
    ];

    protected $casts = [
        'bounding_boxes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modelAI()
    {
        return $this->belongsTo(ModelAI::class, 'model_ai_id');
    }
}