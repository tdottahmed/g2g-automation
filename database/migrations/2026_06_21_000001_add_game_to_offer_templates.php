<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->string('game')->default('clash_of_clans')->after('user_account_id');
            $table->json('game_data')->nullable()->after('game');
        });

        $rows = DB::table('offer_templates')->get();
        foreach ($rows as $row) {
            DB::table('offer_templates')->where('id', $row->id)->update([
                'game'      => 'clash_of_clans',
                'game_data' => json_encode(array_filter([
                    'th_level'       => $row->th_level,
                    'king_level'     => $row->king_level,
                    'queen_level'    => $row->queen_level,
                    'warden_level'   => $row->warden_level,
                    'champion_level' => $row->champion_level,
                ], fn ($v) => $v !== null && $v !== '')),
            ]);
        }

        Schema::table('offer_templates', function (Blueprint $table) {
            $table->dropColumn(['th_level', 'king_level', 'queen_level', 'warden_level', 'champion_level']);
        });
    }

    public function down(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->string('th_level')->nullable();
            $table->string('king_level')->nullable();
            $table->string('queen_level')->nullable();
            $table->string('warden_level')->nullable();
            $table->string('champion_level')->nullable();
        });

        $rows = DB::table('offer_templates')->where('game', 'clash_of_clans')->get();
        foreach ($rows as $row) {
            $gd = json_decode($row->game_data ?? '{}', true) ?? [];
            DB::table('offer_templates')->where('id', $row->id)->update([
                'th_level'       => $gd['th_level'] ?? null,
                'king_level'     => $gd['king_level'] ?? null,
                'queen_level'    => $gd['queen_level'] ?? null,
                'warden_level'   => $gd['warden_level'] ?? null,
                'champion_level' => $gd['champion_level'] ?? null,
            ]);
        }

        Schema::table('offer_templates', function (Blueprint $table) {
            $table->dropColumn(['game', 'game_data']);
        });
    }
};
