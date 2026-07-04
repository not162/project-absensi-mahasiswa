<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_repeats', function (Blueprint $table) {
            $table->string('bukti_foto')->nullable()->after('catatan_admin');
            $table->date('tanggal_ujian_ulang')->nullable()->after('bukti_foto');
        });
    }

    public function down(): void
    {
        Schema::table('course_repeats', function (Blueprint $table) {
            $table->dropColumn(['bukti_foto', 'tanggal_ujian_ulang']);
        });
    }
};
