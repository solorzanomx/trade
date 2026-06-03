<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plantillas de prompts editables
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');           // "Reporte QQQ"
            $table->string('slug')->unique(); // "qqq-daily"
            $table->text('prompt');
            $table->string('symbol', 20)->default('QQQ');
            $table->boolean('is_active')->default(true);
            $table->string('schedule_time', 5)->default('09:35'); // HH:MM
            $table->timestamps();
        });

        // Reportes generados
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_template_id')->constrained()->cascadeOnDelete();
            $table->date('report_date');
            $table->longText('content');      // markdown del reporte
            $table->string('source')->default('claude'); // claude / perplexity
            $table->enum('status', ['pending', 'generated', 'error'])->default('generated');
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'report_template_id', 'report_date']);
            $table->index(['user_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('report_templates');
    }
};
