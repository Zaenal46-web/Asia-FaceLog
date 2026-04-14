<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            // drop unique lama di pin_fingerspot
            // nama index default MySQL/Laravel biasanya: karyawans_pin_fingerspot_unique
            $table->dropUnique('karyawans_pin_fingerspot_unique');
        });

        Schema::table('karyawans', function (Blueprint $table) {
            $table->unique(['device_id', 'pin_fingerspot'], 'uniq_karyawans_device_pin');
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropUnique('uniq_karyawans_device_pin');
        });

        Schema::table('karyawans', function (Blueprint $table) {
            $table->unique('pin_fingerspot', 'karyawans_pin_fingerspot_unique');
        });
    }
};