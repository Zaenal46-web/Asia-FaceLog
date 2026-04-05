<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kategori_shift_jadwals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_shift_id')->constrained('kategori_shifts')->cascadeOnDelete();
            $table->unsignedTinyInteger('hari_ke'); // 0=minggu s/d 6=sabtu
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['kategori_shift_id', 'hari_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_shift_jadwals');
    }
};
