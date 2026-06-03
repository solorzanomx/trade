<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal', 'adjustment']);
            $table->decimal('amount', 12, 2); // siempre positivo
            $table->date('date');
            $table->string('notes')->nullable();
            $table->string('ibkr_ref')->nullable(); // referencia IBKR si viene del XML
            $table->timestamps();

            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
