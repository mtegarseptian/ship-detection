<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('model_a_i_s', function (Blueprint $table) {
            // Konfigurasi Training
            $table->string('base_model')->nullable()->after('description');
            $table->string('epochs')->nullable()->after('base_model');
            $table->string('batch_size')->nullable()->after('epochs');
            $table->string('imgsz')->nullable()->after('batch_size');
            
            // Metrik Akurasi
            $table->float('precision', 8, 4)->nullable()->after('imgsz');
            $table->float('recall', 8, 4)->nullable()->after('precision');
            $table->float('map50', 8, 4)->nullable()->after('recall');
            $table->float('map50_95', 8, 4)->nullable()->after('map50');
            
            // Gambar Grafik
            $table->json('metrics_images')->nullable()->after('map50_95');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('model_a_i_s', function (Blueprint $table) {
            $table->dropColumn([
                'base_model', 'epochs', 'batch_size', 'imgsz',
                'precision', 'recall', 'map50', 'map50_95', 'metrics_images'
            ]);
        });
    }
};