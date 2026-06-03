<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->string('ibkr_order_id', 50)->nullable()->after('strategy');
            $table->string('ibkr_exec_id', 100)->nullable()->after('ibkr_order_id');
            $table->index(['user_id', 'ibkr_order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'ibkr_order_id']);
            $table->dropColumn(['ibkr_order_id', 'ibkr_exec_id']);
        });
    }
};
