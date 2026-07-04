<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teaching_attendances', function (Blueprint $table) {
            $table->time('jam')->nullable()->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('teaching_attendances', function (Blueprint $table) {
            $table->dropColumn('jam');
        });
    }
};
