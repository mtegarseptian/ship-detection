<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelAI extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'description',
        'file_path',
        'file_type',
        'status',
        'uploaded_by',
        
        // Data dari args.yaml (Utama)
        'base_model',
        'epochs',
        'batch_size',
        'imgsz',
        
        // Seluruh data args.yaml utuh
        'args_yaml',
        
        // Data dari results.csv
        'precision',
        'recall',
        'map50',
        'map50_95',
        
        // Data gambar
        'metrics_images',
        'batch_images'
    ];

    // Beritahu Laravel bahwa field ini berbentuk Array (JSON di DB)
    protected $casts = [
        'metrics_images' => 'array',
        'batch_images'   => 'array',
        'args_yaml'      => 'array',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}