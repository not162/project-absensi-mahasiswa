<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // dosen
            $table->enum('hari', ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->enum('mode', ['offline', 'online'])->default('offline');
            // Untuk offline: nama ruangan, e.g. "GD3-201"
            // Untuk online: kode/link, e.g. "ZOOM-XYZ123" atau "meet.google.com/abc"
            $table->string('ruangan')->nullable();
            $table->string('link_online')->nullable(); // khusus mode online
            $table->string('kode_online')->nullable(); // kode meeting online
            $table->string('tahun_ajaran')->default('2025/2026');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};