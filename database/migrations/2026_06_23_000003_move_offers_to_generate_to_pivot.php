<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer_template_user_account', function (Blueprint $table) {
            $table->unsignedInteger('offers_to_generate')->default(0)->after('user_account_id');
        });

        // Migrate existing per-template queue counts to all linked pivot rows
        DB::table('offer_templates')
            ->where('offers_to_generate', '>', 0)
            ->orderBy('id')
            ->each(function ($template) {
                DB::table('offer_template_user_account')
                    ->where('offer_template_id', $template->id)
                    ->update(['offers_to_generate' => $template->offers_to_generate]);
            });

        Schema::table('offer_templates', function (Blueprint $table) {
            $table->dropColumn('offers_to_generate');
        });
    }

    public function down(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->unsignedInteger('offers_to_generate')->default(0)->after('is_permanent');
        });

        // Restore: sum pivot counts back to template (take first pivot row's value per template)
        DB::table('offer_template_user_account')
            ->where('offers_to_generate', '>', 0)
            ->select('offer_template_id', DB::raw('MAX(offers_to_generate) as max_count'))
            ->groupBy('offer_template_id')
            ->get()
            ->each(function ($row) {
                DB::table('offer_templates')
                    ->where('id', $row->offer_template_id)
                    ->update(['offers_to_generate' => $row->max_count]);
            });

        Schema::table('offer_template_user_account', function (Blueprint $table) {
            $table->dropColumn('offers_to_generate');
        });
    }
};
