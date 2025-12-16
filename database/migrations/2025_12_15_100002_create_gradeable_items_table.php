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
        Schema::create('gradeable_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('gradeable_categories')->onDelete('cascade');
            $table->string('title', 255);
            $table->decimal('max_points', 5, 2)->default(100.00);
            $table->timestamps();

            // Index for query performance
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gradeable_items');
    }
};
