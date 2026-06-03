<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Identidad
            $table->string('symbol', 20);
            $table->string('name')->nullable();
            $table->enum('asset_type', ['stock', 'etf', 'crypto', 'bond', 'reit'])->default('stock');
            $table->string('sector', 100)->nullable();

            // Posición
            $table->decimal('shares', 16, 6);
            $table->decimal('avg_cost', 16, 4);       // costo promedio por acción
            $table->decimal('current_price', 16, 4)->nullable();  // precio manual actualizable
            $table->decimal('target_price', 16, 4)->nullable();
            $table->decimal('stop_loss', 16, 4)->nullable();

            // Income
            $table->decimal('dividends_received', 12, 2)->default(0);

            // Metadata
            $table->date('entry_date');
            $table->text('thesis')->nullable();        // tesis de inversión
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');

            // Cierre
            $table->decimal('exit_price', 16, 4)->nullable();
            $table->date('exit_date')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_positions');
    }
};
