<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->boolean('is_permanent')->default(false)->after('queue_delete');
        });

        Schema::table('offer_templates', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'queue_delete']);
        });
    }

    public function down(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->boolean('is_active')->default(false);
            $table->boolean('queue_delete')->default(false);
            $table->dropColumn('is_permanent');
        });
    }
};
