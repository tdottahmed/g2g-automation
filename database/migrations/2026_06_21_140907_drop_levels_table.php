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
        Schema::dropIfExists('levels');
    }

    public function down(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->string('type');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
};
