<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')
                ->nullable()
                ->change();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->unique(
                'user_id',
                'addresses_user_id_unique'
            );
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->unique(
                'user_id',
                'student_profiles_user_id_unique'
            );
        });

        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->unique(
                'user_id',
                'teacher_profiles_user_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropUnique(
                'teacher_profiles_user_id_unique'
            );
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropUnique(
                'student_profiles_user_id_unique'
            );
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropUnique(
                'addresses_user_id_unique'
            );
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')
                ->nullable(false)
                ->change();
        });
    }
};