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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // Auto-increment ID
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->json('seo_teg')->nullable(); 
            $table->string('total_video')->nullable();
            $table->float('category_star_rate', 8, 2)->nullable(); 
            $table->integer('sorting_postion')->nullable();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
