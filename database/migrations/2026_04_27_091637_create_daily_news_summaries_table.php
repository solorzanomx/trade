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
        Schema::create('daily_news_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->longText('summary');
            $table->enum('source', ['perplexity', 'claude', 'manual'])->default('claude');
            $table->json('key_points')->nullable();
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'asset_id', 'date']);
            $table->index('user_id');
            $table->index('asset_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_news_summaries');
    }
};
