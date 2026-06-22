<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferTemplate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'medias'                   => 'array',
        'delivery_method'          => 'array',
        'wholesale_pricing'        => 'array',
        'game_data'                => 'array',
        'is_permanent'             => 'boolean',
        'enable_low_stock_alert'   => 'boolean',
        'instant_delivery'         => 'boolean',
        'enable_wholesale_pricing' => 'boolean',
        'last_posted_at'           => 'datetime',
    ];

    public const GAMES = [
        'clash_of_clans'      => 'Clash of Clans',
        'brawl_stars'         => 'Brawl Stars',
        'clash_royale'        => 'Clash Royale',
        'hay_day'             => 'Hay Day',
        'mobile_legends'      => 'Mobile Legends',
        'call_of_duty_mobile' => 'Call of Duty Mobile',
    ];

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class);
    }

    public function logs()
    {
        return $this->hasMany(OfferAutomationLog::class);
    }

    public function shouldPostNow(int $intervalMinutes): bool
    {
        if (!$this->last_posted_at) {
            return true;
        }
        return $this->last_posted_at->addMinutes($intervalMinutes)->isPast();
    }

    public function gameLabel(): string
    {
        return self::GAMES[$this->game] ?? $this->game;
    }

    public function gameBadgeClass(): string
    {
        return match($this->game) {
            'clash_of_clans'      => 'bg-primary',
            'brawl_stars'         => 'bg-warning text-dark',
            'clash_royale'        => 'bg-info text-dark',
            'hay_day'             => 'bg-success',
            'mobile_legends'      => 'bg-danger',
            'call_of_duty_mobile' => 'bg-dark',
            default               => 'bg-secondary',
        };
    }

    public function gameSummary(): string
    {
        $d = $this->game_data ?? [];
        return match($this->game) {
            'clash_of_clans' => implode(' · ', array_filter([
                isset($d['th_level']) ? 'TH' . $d['th_level'] : null,
                isset($d['king_level']) ? 'K' . $d['king_level'] : null,
                isset($d['queen_level']) ? 'Q' . $d['queen_level'] : null,
            ])),
            'brawl_stars' => implode(' · ', array_filter([
                $d['platform'] ?? null,
                isset($d['trophies']) ? number_format((int) $d['trophies']) . ' Trophies' : null,
                isset($d['brawlers']) ? $d['brawlers'] . ' Brawlers' : null,
            ])),
            'clash_royale' => implode(' · ', array_filter([
                isset($d['king_level']) ? 'K' . $d['king_level'] : null,
                $d['arena'] ?? null,
                isset($d['level_16_cards']) ? $d['level_16_cards'] . ' Lvl16' : null,
            ])),
            'hay_day'             => $d['platform'] ?? '',
            'mobile_legends'      => implode(' · ', array_filter([
                $d['platform'] ?? null,
                $d['rank'] ?? null,
                isset($d['heroes']) ? $d['heroes'] . ' Heroes' : null,
            ])),
            'call_of_duty_mobile' => implode(' · ', array_filter([
                $d['platform'] ?? null,
                $d['rank'] ?? null,
            ])),
            default => '',
        };
    }
}
