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
        Schema::create('user_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('manga_id'); // MangaDex manga ID (UUID)
            $table->string('manga_title')->nullable(); // Cache manga title
            $table->string('cover_url')->nullable(); // Cache cover URL for performance
            $table->text('notes')->nullable(); // User's personal notes about this manga
            $table->boolean('is_reading')->default(true); // Currently reading or marked for later
            $table->timestamps();

            $table->unique(['user_id', 'manga_id']);
            $table->index(['user_id', 'is_reading']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bookmarks');
    }
};
