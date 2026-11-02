<?php

namespace App\Models;

use App\TicketSheet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo_path', 'contact_phone', 'contact_email', 'address', 'ticket_footer', 'ticket_paper', 'show_signatures', 'show_commission', 'priority_days', 'report_period', 'ticket_preferences'];

    protected $attributes = [
        'name' => 'Ktontine', 'contact_phone' => '', 'contact_email' => '', 'address' => '', 'ticket_footer' => '',
        'ticket_paper' => 'A4', 'show_signatures' => true, 'show_commission' => true, 'priority_days' => 7, 'report_period' => 'month',
    ];

    protected function casts(): array
    {
        return ['ticket_preferences' => 'array', 'show_signatures' => 'boolean', 'show_commission' => 'boolean', 'priority_days' => 'integer'];
    }

    public function logoUrl(): string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::disk('public')->url($this->logo_path);
        }

        return asset('ktontine-logo.png');
    }

    public static function current(): self
    {
        $settings = static::query()->find(1) ?? (new static)->forceFill(['id' => 1]);
        $settings->ticket_preferences = array_replace(TicketSheet::PREFERENCES, array_intersect_key($settings->ticket_preferences ?? [], TicketSheet::PREFERENCES));

        return $settings;
    }
}
