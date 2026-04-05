<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('holiday_calendar_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holiday_calendar_id')->constrained('holiday_calendars')->cascadeOnDelete();
            $table->foreignId('kategori_karyawan_id')->constrained('kategori_karyawans')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['holiday_calendar_id', 'kategori_karyawan_id'], 'holiday_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_calendar_categories');
    }
};
