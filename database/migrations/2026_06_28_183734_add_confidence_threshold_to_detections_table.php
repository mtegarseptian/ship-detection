<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('detections', function (Blueprint $table) {
            // Tambahkan kolom float untuk menyimpan angka desimal seperti 0.85
            $table->float('confidence_threshold')->default(0.85)->after('ship_count');
        });
    }

    public function down()
    {
        Schema::table('detections', function (Blueprint $table) {
            $table->dropColumn('confidence_threshold');
        });
    }
};