<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom kode_dosen & update enum role
        Schema::table('users', function (Blueprint $table) {
            $table->string('kode_dosen')->nullable()->unique()->after('nim');
        });

        // Update enum role agar support 'dosen'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user','dosen') DEFAULT 'user'");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kode_dosen');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user') DEFAULT 'user'");
    }
};