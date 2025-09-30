<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_offer_automation_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_template_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('status', ['success', 'failed']); // Simplified status
            $table->text('message'); // Simple success/failure message
            $table->json('details')->nullable(); // All steps, output, errors in one place
            $table->timestamp('executed_at'); // When the attempt completed
            $table->timestamps();

            $table->index('status');
            $table->index('executed_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_automation_logs');
    }
};
