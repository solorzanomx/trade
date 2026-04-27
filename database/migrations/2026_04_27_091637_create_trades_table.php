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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_id')->nullable()->constrained()->onDelete('set null');

            // Trade Basics
            $table->string('symbol'); // AAPL, QQQ, etc
            $table->enum('trade_type', ['stock', 'call', 'put'])->default('stock');
            $table->enum('position_direction', ['long', 'short'])->default('long');

            // Entry Details
            $table->decimal('entry_price', 12, 4);
            $table->date('entry_date');
            $table->time('entry_time')->nullable();
            $table->text('entry_reason')->nullable();

            // Exit Details
            $table->decimal('exit_price', 12, 4)->nullable();
            $table->date('exit_date')->nullable();
            $table->time('exit_time')->nullable();
            $table->text('exit_reason')->nullable();

            // Trade Parameters
            $table->integer('quantity');
            $table->decimal('capital_used', 12, 2);
            $table->decimal('stop_loss', 12, 4)->nullable();
            $table->decimal('take_profit', 12, 4)->nullable();

            // Calculated Metrics
            $table->decimal('p_l', 12, 2)->nullable();
            $table->decimal('p_l_percent', 8, 4)->nullable();

            // Status & Notes
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->string('emotional_state')->nullable();
            $table->text('mistakes_made')->nullable();

            $table->timestamps();
            $table->index('user_id');
            $table->index('symbol');
            $table->index('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
