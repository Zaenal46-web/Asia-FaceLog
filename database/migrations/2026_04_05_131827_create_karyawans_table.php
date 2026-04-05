<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('pin_fingerspot')->nullable()->unique();
            $table->foreignId('kategori_karyawan_id')->nullable()->constrained('kategori_karyawans')->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('fingerspot_devices')->nullOnDelete();
            $table->string('jabatan')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('status_kerja')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('nama');
            $table->index('kategori_karyawan_id');
            $table->index('device_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
