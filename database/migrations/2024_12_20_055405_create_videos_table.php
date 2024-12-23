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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('video')->nullable();
            $table->json('actor_id')->nullable(); 
            $table->string('category_id')->nullable(); 
            $table->string('channel_id')->nullable();;
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);
            $table->text('description')->nullable();
            $table->json('seo_teg')->nullable(); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
