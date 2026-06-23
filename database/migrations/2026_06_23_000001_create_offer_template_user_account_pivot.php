<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_template_user_account', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_account_id')->constrained()->onDelete('cascade');
            $table->unique(['offer_template_id', 'user_account_id'], 'otua_template_account_unique');
            $table->timestamps();
        });

        // Migrate existing single-account associations to pivot
        DB::table('offer_templates')
            ->whereNotNull('user_account_id')
            ->orderBy('id')
            ->each(function ($template) {
                DB::table('offer_template_user_account')->insertOrIgnore([
                    'offer_template_id' => $template->id,
                    'user_account_id'   => $template->user_account_id,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            });

        Schema::table('offer_templates', function (Blueprint $table) {
            $table->dropForeign(['user_account_id']);
            $table->dropColumn('user_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->foreignId('user_account_id')->nullable()->constrained('user_accounts')->onDelete('cascade');
        });

        // Restore first account from pivot for each template
        DB::table('offer_template_user_account')
            ->select('offer_template_id', DB::raw('MIN(user_account_id) as user_account_id'))
            ->groupBy('offer_template_id')
            ->get()
            ->each(function ($row) {
                DB::table('offer_templates')
                    ->where('id', $row->offer_template_id)
                    ->update(['user_account_id' => $row->user_account_id]);
            });

        Schema::dropIfExists('offer_template_user_account');
    }
};
