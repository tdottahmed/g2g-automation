<?php

namespace Database\Seeders;

use App\Models\OfferScheduler;
use App\Models\UserAccount;
use Illuminate\Database\Seeder;

class OfferSchedulerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: Create a default scheduler for the first user account
        $firstUserAccount = UserAccount::first();

        if ($firstUserAccount) {
            OfferScheduler::create([
                'user_account_id' => $firstUserAccount->id,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'timezone' => config('app.timezone', 'UTC'),
                'days' => ['mon', 'tue', 'wed', 'thu', 'fri'], // Weekdays only
                'posts_per_cycle' => 2,
                'interval_minutes' => 60,
                'max_posts_per_day' => 10,
                'is_active' => true,
            ]);

            $this->command->info('Default scheduler created for user account: ' . $firstUserAccount->email);
        } else {
            $this->command->warn('No user accounts found. Please create a user account first.');
        }

        // You can add more example schedulers here
        // Example: Weekend scheduler
        if ($firstUserAccount) {
            OfferScheduler::create([
                'user_account_id' => $firstUserAccount->id,
                'start_time' => '10:00',
                'end_time' => '16:00',
                'timezone' => config('app.timezone', 'UTC'),
                'days' => ['sat', 'sun'], // Weekends only
                'posts_per_cycle' => 1,
                'interval_minutes' => 120,
                'max_posts_per_day' => 5,
                'is_active' => false, // Disabled by default
            ]);

            $this->command->info('Weekend scheduler created (disabled) for user account: ' . $firstUserAccount->email);
        }
    }
}
