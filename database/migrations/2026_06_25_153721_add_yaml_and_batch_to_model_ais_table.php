<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('model_a_i_s', function (Blueprint $table) {
            // Menambahkan kolom json untuk menyimpan seluruh data args.yaml
            $table->json('args_yaml')->nullable()->after('imgsz');
            // Menambahkan kolom json untuk memisahkan gambar batch dari grafik biasa
            $table->json('batch_images')->nullable()->after('metrics_images');
        });
    }

    public function down()
    {
        Schema::table('model_a_i_s', function (Blueprint $table) {
            $table->dropColumn(['args_yaml', 'batch_images']);
        });
    }
};