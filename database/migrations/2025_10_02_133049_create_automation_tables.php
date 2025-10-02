<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationTables extends Migration
{
    public function up()
    {
        // Automation sessions table
        Schema::create('automation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_account_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->enum('status', ['running', 'completed', 'failed', 'stopped'])->default('running');
            $table->integer('total_templates');
            $table->integer('processed_templates')->default(0);
            $table->integer('successful_posts')->default(0);
            $table->integer('failed_posts')->default(0);
            $table->text('error_log')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_account_id', 'status']);
            $table->index('session_id');
        });

        // Real-time progress tracking
        Schema::create('automation_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('offer_template_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
            $table->integer('current_step')->default(0);
            $table->integer('total_steps')->default(0);
            $table->text('current_action')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['automation_session_id', 'status']);
            $table->index('offer_template_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('automation_progress');
        Schema::dropIfExists('automation_sessions');
    }
}
