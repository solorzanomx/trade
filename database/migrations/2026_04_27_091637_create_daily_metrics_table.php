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
        Schema::create('daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('trades_count')->default(0);
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->decimal('win_rate', 5, 2)->nullable();
            $table->decimal('daily_pnl', 12, 2)->nullable();
            $table->decimal('daily_pnl_percent', 8, 4)->nullable();
            $table->string('best_trade')->nullable();
            $table->string('worst_trade')->nullable();
            $table->decimal('avg_risk_reward', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'date']);
            $table->index('user_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_metrics');
    }
};
