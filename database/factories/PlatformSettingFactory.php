<?php

namespace Database\Factories;

use App\Models\PlatformSetting;
use App\TicketSheet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformSetting>
 */
class PlatformSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['name' => 'Ktontine', 'ticket_preferences' => TicketSheet::PREFERENCES];
    }
}
