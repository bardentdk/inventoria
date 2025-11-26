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
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            // Lien vers le ticket parent. Si le ticket est supprimé, on supprime les messages.
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            // L'auteur du message (User ou Admin)
            $table->foreignId('user_id')->constrained();
            $table->text('message'); // Le contenu du message
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};