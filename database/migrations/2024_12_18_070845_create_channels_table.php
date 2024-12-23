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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('subscriber')->default(0);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('seo_teg')->nullable();
            $table->integer('like')->default(0);
            $table->integer('dislike')->default(0);
            $table->float('ratting')->default(0);
            $table->integer('sorting_position')->nullable();
            $table->integer('total_image')->default(0);
            $table->integer('total_video')->default(0);
            $table->integer('total_view')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
