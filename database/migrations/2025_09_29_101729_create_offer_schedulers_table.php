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
        Schema::create('offer_schedulers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_template_id')->nullable()->constrained('offer_templates')->onDelete('cascade');

            // Scheduling configuration
            $table->time('start_time')->default('09:00'); // Start time (e.g., 09:00)
            $table->time('end_time')->default('17:00');   // End time (e.g., 17:00)

            // Rate limiting
            $table->integer('posts_per_cycle')->default(1); // How many offers to post per cycle
            $table->integer('interval_minutes')->default(60); // Minutes between posts
            $table->integer('max_posts_per_day')->nullable(); // Daily limit (null = unlimited)

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->integer('posts_today')->default(0);
            $table->date('posts_today_date')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['offer_template_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_schedulers');
    }
};
