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
        \App\Models\Role::create(['name' => 'jefe cerrada']);
        \App\Models\Role::create(['name' => 'guardia']); 
        \App\Models\Role::create(['name' => 'jefe de familia']); // Rol por defecto
        \App\Models\Role::create(['name' => 'familiar']);


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
