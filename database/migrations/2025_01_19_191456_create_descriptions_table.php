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
        Schema::create('descriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('model',100);
            $table->string('eng_capacity',100);
            $table->string('dis_travel',100);
            $table->double('price');
            $table->string('type_car', 20);
            $table->string('fuel_consumption', 20);
            $table->string('drive_system', 30);
            $table->integer('number_Seats');
            $table->boolean('cruise_control_system');
            $table->foreignId('cars_id')->constrained('cars')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('posts_id')->constrained('posts')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descriptions');
    }
};
