<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel 'dosens' sudah tidak dipakai. Data dosen kini disimpan di 'users' (role='dosen').
        Schema::dropIfExists('dosens');
    }

    public function down(): void
    {
        Schema::create('dosens', function ($table) {
            $table->id();
            $table->string('nama');
            $table->string('kode_dosen')->unique();
            $table->string('email')->unique();
            $table->string('prodi');
            $table->timestamps();
        });
    }
};
