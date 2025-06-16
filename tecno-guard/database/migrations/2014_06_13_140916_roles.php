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
        Schema::create('roles', function (Blueprint $table) {
            $table->id(); // Equivalente a BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('name', 64)->unique();
            $table->timestamps(); // created_at y updated_at
        });

        // Seeders básicos
        \App\Models\Role::create(['name' => 'admin']);
        \App\Models\Role::create(['name' => 'editor']);
        \App\Models\Role::create(['name' => 'user']); // Rol por defecto
        \App\Models\Role::create(['name' => 'guest']);
        \App\Models\Role::create(['name' => 'superadmin']);


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
