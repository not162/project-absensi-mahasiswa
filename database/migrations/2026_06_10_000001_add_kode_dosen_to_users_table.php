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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user','dosen') DEFAULT 'user'");
        } elseif (DB::getDriverName() === 'sqlite') {
            Schema::disableForeignKeyConstraints();
            Schema::create('users_temp', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('nim')->nullable()->unique();
                $table->string('kode_dosen')->nullable()->unique();
                $table->string('phone')->nullable();
                $table->enum('role', ['admin', 'user', 'dosen'])->default('user');
                $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
            DB::statement('INSERT INTO users_temp (id, name, email, nim, kode_dosen, phone, role, department_id, email_verified_at, password, remember_token, created_at, updated_at) SELECT id, name, email, nim, kode_dosen, phone, role, department_id, email_verified_at, password, remember_token, created_at, updated_at FROM users');
            Schema::drop('users');
            Schema::rename('users_temp', 'users');
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('kode_dosen');
            });
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user') DEFAULT 'user'");
        } elseif (DB::getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('kode_dosen');
            });
        }
    }
};