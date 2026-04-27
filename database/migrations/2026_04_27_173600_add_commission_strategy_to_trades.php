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
        Schema::table('trades', function (Blueprint $table) {
            $table->decimal('commission', 8, 2)->nullable()->after('take_profit');
            $table->decimal('net_p_l', 12, 2)->nullable()->after('p_l_percent');
            $table->string('strategy')->nullable()->after('net_p_l');
            $table->string('setup_quality')->nullable()->after('strategy');
            $table->boolean('followed_plan')->nullable()->after('setup_quality');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn(['commission', 'net_p_l', 'strategy', 'setup_quality', 'followed_plan']);
        });
    }
};
