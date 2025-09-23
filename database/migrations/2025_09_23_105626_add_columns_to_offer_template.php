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
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->timestamp('last_posted_at')->nullable();
            $table->integer('offers_to_generate')->default(1); // number of offers remaining to generate, optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->dropColumn('last_posted_at');
            $table->dropColumn('offers_to_generate');
        });
    }
};
