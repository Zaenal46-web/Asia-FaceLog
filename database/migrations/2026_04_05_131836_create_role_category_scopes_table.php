<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_category_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('kategori_karyawan_id')->constrained('kategori_karyawans')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'kategori_karyawan_id'], 'role_category_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_category_scopes');
    }
};
