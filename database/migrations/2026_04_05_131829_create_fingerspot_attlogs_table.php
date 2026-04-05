<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fingerspot_attlogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('fingerspot_devices')->nullOnDelete();
            $table->string('pin');
            $table->string('device_sn')->nullable();
            $table->dateTime('scan_time');
            $table->string('verify_mode')->nullable();
            $table->string('status_scan')->nullable();
            $table->text('photo_url')->nullable();
            $table->longText('raw')->nullable();
            $table->timestamps();

            $table->index('pin');
            $table->index('scan_time');
            $table->index(['device_id', 'pin', 'scan_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerspot_attlogs');
    }
};
