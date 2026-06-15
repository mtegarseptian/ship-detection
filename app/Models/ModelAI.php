<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelAI extends Model
{
    protected $table = 'model_a_i_s';

    protected $fillable = [
        'name', 'version', 'file_path', 'file_type',
        'description', 'status', 'uploaded_by',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function detections()
    {
        return $this->hasMany(Detection::class, 'model_ai_id');
    }
}