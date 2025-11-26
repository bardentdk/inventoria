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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Demandeur
            $table->foreignId('assigned_to')->nullable()->constrained('users'); // Technicien
            $table->foreignId('asset_id')->nullable()->constrained(); // Si lié à une panne PC
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['it_issue', 'logistics', 'supply_request']);
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
