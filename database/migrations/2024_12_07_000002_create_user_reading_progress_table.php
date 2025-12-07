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
        Schema::create('user_reading_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('manga_id'); // MangaDex manga ID (UUID)
            $table->string('chapter_id')->nullable(); // MangaDex chapter ID (UUID)
            $table->string('chapter_number')->nullable(); // Chapter number (e.g., "12.5", "1")
            $table->integer('page')->default(0); // Last page read (1-indexed)
            $table->integer('total_pages')->default(0); // Total pages in chapter
            $table->text('manga_title')->nullable(); // Cache for display
            $table->text('chapter_title')->nullable(); // Cache for display
            $table->string('cover_url')->nullable(); // Cache cover URL for performance
            $table->timestamps();

            $table->unique(['user_id', 'manga_id']);
            $table->index(['user_id', 'updated_at']);
            $table->index(['chapter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reading_progress');
    }
};
