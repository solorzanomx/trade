<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            // Pre-market
            $table->enum('pre_bias', ['bullish', 'bearish', 'neutral'])->nullable();
            $table->text('pre_plan')->nullable();
            $table->json('watchlist')->nullable();
            $table->text('pre_goals')->nullable();
            $table->unsignedTinyInteger('mood_start')->nullable();
            $table->text('market_conditions')->nullable();
            // Post-market
            $table->enum('grade', ['A+','A','B','C','D','F'])->nullable();
            $table->text('what_went_well')->nullable();
            $table->text('what_to_improve')->nullable();
            $table->text('lesson_learned')->nullable();
            $table->text('goals_tomorrow')->nullable();
            $table->unsignedTinyInteger('mood_end')->nullable();
            $table->text('post_review')->nullable();
            $table->boolean('followed_plan')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'entry_date']);
        });

        Schema::create('trading_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('max_daily_loss', 10, 2)->default(500);
            $table->decimal('max_weekly_loss', 10, 2)->default(1500);
            $table->decimal('max_position_size_pct', 5, 2)->default(2);
            $table->unsignedSmallInteger('max_trades_per_day')->default(3);
            $table->text('rules')->nullable();
            $table->json('pre_trade_checklist')->nullable();
            $table->json('allowed_setups')->nullable();
            $table->text('trading_schedule')->nullable();
            $table->text('market_conditions_allowed')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('trading_plans');
    }
};
