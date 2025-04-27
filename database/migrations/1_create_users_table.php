<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'monitor', 'candidate'])->default('candidate');
            $table->string('address')->nullable();
            $table->string('phone_number');
            $table->string('profile_picture')->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated', 'rejected'])->default('inactive');
            $table->date('date_of_birth');
            $table->rememberToken()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
