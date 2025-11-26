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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ex: MacBook Pro M3
            $table->string('serial_number')->unique();
            $table->string('inventory_code')->unique(); // NEXA-001
            $table->foreignId('category_id')->constrained(); // Laptop, Écran, Chaise
            $table->foreignId('user_id')->nullable()->constrained(); // Assigné à qui ?
            $table->enum('status', ['available', 'assigned', 'broken', 'repair']);
            $table->text('specs')->nullable(); // JSON ou Text pour config
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
