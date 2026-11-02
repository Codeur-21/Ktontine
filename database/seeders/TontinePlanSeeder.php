<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class TontinePlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(['slug' => 'standard'], ['name' => 'Standard', 'price' => config('tontine.default_monthly_price_gnf'), 'trial_days' => config('tontine.default_trial_days'), 'is_active' => true]);
    }
}
