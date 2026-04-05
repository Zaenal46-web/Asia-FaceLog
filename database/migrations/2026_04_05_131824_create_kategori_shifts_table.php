<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kategori_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_karyawan_id')->constrained('kategori_karyawans')->cascadeOnDelete();
            $table->foreignId('shift_master_id')->constrained('shift_masters')->cascadeOnDelete();
            $table->string('nama_rule')->nullable();
            $table->unsignedInteger('prioritas')->default(1);
            $table->boolean('is_default')->default(false);
            $table->boolean('lintas_hari')->default(false);

            $table->integer('window_masuk_mulai_menit')->default(-120);
            $table->integer('window_masuk_selesai_menit')->default(180);
            $table->integer('window_pulang_mulai_menit')->default(-180);
            $table->integer('window_pulang_selesai_menit')->default(240);

            $table->unsignedInteger('toleransi_telat_menit')->default(0);
            $table->unsignedInteger('toleransi_pulang_cepat_menit')->default(0);
            $table->unsignedInteger('toleransi_lembur_menit')->default(0);

            $table->boolean('istirahat_aktif')->default(false);
            $table->boolean('istirahat_otomatis_potong')->default(false);
            $table->unsignedInteger('menit_istirahat_default')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['kategori_karyawan_id', 'prioritas']);
            $table->index(['shift_master_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_shifts');
    }
};
