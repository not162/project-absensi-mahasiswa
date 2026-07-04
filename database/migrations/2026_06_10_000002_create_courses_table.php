<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('kode_matkul')->unique();    // e.g. IF-101
            $table->string('nama_matkul');
            $table->integer('sks')->default(2);
            $table->string('semester');                 // e.g. "Ganjil 2025/2026"
            $table->foreignId('department_id')
                  ->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};