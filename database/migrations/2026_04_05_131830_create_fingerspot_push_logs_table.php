<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fingerspot_push_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('fingerspot_devices')->nullOnDelete();
            $table->string('pin')->nullable();
            $table->string('action');
            $table->longText('payload_json')->nullable();
            $table->string('status')->default('pending');
            $table->text('response_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'status']);
            $table->index('pin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerspot_push_logs');
    }
};
