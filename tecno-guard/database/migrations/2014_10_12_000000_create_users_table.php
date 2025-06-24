<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 127);
            $table->string('email', 127)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 127);
            $table->string('phone', 10);
            $table->unsignedBigInteger('family_id')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('family_group_isactive')->nullable();
            $table->string('direccion', 255)->nullable();
            $table->boolean('direccion_verified')->nullable();
            $table->string('code', 255)->nullable();
            $table->unsignedBigInteger('role_id')->default(4);
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_code')->nullable();
            $table->timestamp('two_factor_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
