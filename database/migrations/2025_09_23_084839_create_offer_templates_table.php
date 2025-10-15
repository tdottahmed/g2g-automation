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
        Schema::create('offer_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_account_id')->constrained('user_accounts')->onDelete('cascade');
            $table->string('title');
            $table->longText('description');
            $table->string('th_level')->nullable();
            $table->string('king_level')->nullable();
            $table->string('queen_level')->nullable();
            $table->string('warden_level')->nullable();
            $table->string('champion_level')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('medias')->nullable();
            $table->string('currency')->default('USD');
            $table->decimal('price')->default(0);
            $table->string('delivery_method')->nullable();
            $table->boolean('enable_low_stock_alert')->default(false);
            $table->integer('low_stock_threshold')->nullable();
            $table->integer('minimum_order_quantity')->default(1);
            $table->boolean('instant_delivery')->default(true);
            $table->boolean('enable_wholesale_pricing')->default(false);
            $table->json('wholesale_pricing')->nullable();
            $table->string('region')->default('Global');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_templates');
    }
};
