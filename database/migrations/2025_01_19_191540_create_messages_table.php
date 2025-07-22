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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->string('state',50);
            $table->foreignId('sender_users_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('resiver_users_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
