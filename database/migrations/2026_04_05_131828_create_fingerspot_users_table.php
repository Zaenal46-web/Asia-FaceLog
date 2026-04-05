<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fingerspot_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('fingerspot_devices')->cascadeOnDelete();
            $table->string('pin');
            $table->string('nama')->nullable();
            $table->string('privilege')->nullable();
            $table->string('password')->nullable();
            $table->string('rfid')->nullable();
            $table->unsignedInteger('face_template_count')->nullable();
            $table->unsignedInteger('finger_template_count')->nullable();
            $table->unsignedInteger('vein_template_count')->nullable();
            $table->longText('raw_json')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['device_id', 'pin']);
            $table->index('pin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerspot_users');
    }
};
