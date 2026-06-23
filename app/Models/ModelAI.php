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
        
        // Data dari args.yaml
        'base_model',
        'epochs',
        'batch_size',
        'imgsz',
        
        // Data dari results.csv
        'precision',
        'recall',
        'map50',
        'map50_95',
        
        // Data gambar grafik
        'metrics_images'
    ];

    // Beritahu Laravel bahwa metrics_images berbentuk Array (JSON)
    protected $casts = [
        'metrics_images' => 'array',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}