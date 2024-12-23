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
            $table->id(); 
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->json('seo_teg')->nullable(); 
            $table->string('total_video')->default(0);
            $table->float('category_star_rate', 8, 2)->nullable(); 
            $table->integer('sorting_position')->nullable();
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
