<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('holiday_calendars', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('tipe'); // national, company, category_specific
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tanggal_mulai', 'tanggal_selesai']);
            $table->index('tipe');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_calendars');
    }
};
