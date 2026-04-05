<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fingerspot_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('fingerspot_devices')->nullOnDelete();
            $table->string('event_type')->nullable();
            $table->longText('payload_json')->nullable();
            $table->string('status')->default('received');
            $table->text('message')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index('event_type');
            $table->index('status');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerspot_webhook_logs');
    }
};
