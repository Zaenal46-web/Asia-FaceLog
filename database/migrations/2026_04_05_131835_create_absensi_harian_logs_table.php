<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absensi_harian_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absensi_harian_id')->constrained('absensi_harians')->cascadeOnDelete();
            $table->string('action');
            $table->text('notes')->nullable();
            $table->longText('payload_json')->nullable();
            $table->timestamps();

            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_harian_logs');
    }
};
