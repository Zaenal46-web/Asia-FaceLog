<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absensi_harians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('fingerspot_devices')->nullOnDelete();
            $table->foreignId('kategori_karyawan_id')->nullable()->constrained('kategori_karyawans')->nullOnDelete();
            $table->foreignId('kategori_shift_id')->nullable()->constrained('kategori_shifts')->nullOnDelete();
            $table->foreignId('shift_master_id')->nullable()->constrained('shift_masters')->nullOnDelete();
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('status_hadir')->default('alpha');
            $table->boolean('status_telat')->default(false);
            $table->unsignedInteger('menit_telat')->default(0);
            $table->boolean('status_pulang_cepat')->default(false);
            $table->unsignedInteger('menit_pulang_cepat')->default(0);
            $table->boolean('status_lembur')->default(false);
            $table->unsignedInteger('menit_lembur')->default(0);
            $table->unsignedInteger('total_menit_kerja')->default(0);
            $table->unsignedInteger('total_menit_istirahat')->default(0);
            $table->dateTime('first_scan_at')->nullable();
            $table->dateTime('last_scan_at')->nullable();
            $table->unsignedInteger('scan_count')->default(0);
            $table->string('source')->default('fingerspot_attlogs');
            $table->boolean('is_manual')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->foreignId('holiday_calendar_id')->nullable()->constrained('holiday_calendars')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['karyawan_id', 'tanggal']);
            $table->index('tanggal');
            $table->index('device_id');
            $table->index('kategori_karyawan_id');
            $table->index('shift_master_id');
            $table->index('status_hadir');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_harians');
    }
};
