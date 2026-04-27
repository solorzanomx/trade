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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('symbol')->unique();
            $table->string('name');
            $table->enum('asset_type', ['stock', 'etf', 'crypto'])->default('stock');
            $table->string('sector')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('user_id');
            $table->index('symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
