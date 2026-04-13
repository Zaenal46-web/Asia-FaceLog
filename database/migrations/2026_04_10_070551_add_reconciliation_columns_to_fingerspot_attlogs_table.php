<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fingerspot_attlogs', function (Blueprint $table) {
            $table->string('source_channel', 30)->nullable()->after('photo_url');
            $table->timestamp('received_at')->nullable()->after('source_channel');
            $table->string('vendor_trans_id', 100)->nullable()->after('received_at');
            $table->string('sync_batch', 100)->nullable()->after('vendor_trans_id');

            $table->index(['device_id', 'pin', 'scan_time'], 'idx_attlog_device_pin_scan');
            $table->index(['source_channel', 'received_at'], 'idx_attlog_source_received');
        });
    }

    public function down(): void
    {
        Schema::table('fingerspot_attlogs', function (Blueprint $table) {
            $table->dropIndex('idx_attlog_device_pin_scan');
            $table->dropIndex('idx_attlog_source_received');

            $table->dropColumn([
                'source_channel',
                'received_at',
                'vendor_trans_id',
                'sync_batch',
            ]);
        });
    }
};