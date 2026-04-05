<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fingerspot_devices', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('serial_number')->nullable()->unique();
            $table->string('cloud_id')->nullable()->index();
            $table->string('lokasi')->nullable();
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('ip_address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerspot_devices');
    }
};
