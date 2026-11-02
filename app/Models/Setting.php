<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'settings';

    protected $guarded = ['id'];

    protected $attributes = [
        'contact_phone' => '',
        'contact_email' => '',
        'address' => '',
        'ticket_footer' => '',
        'ticket_paper' => 'A4',
        'show_signatures' => true,
        'show_commission' => true,
        'priority_days' => 7,
        'report_period' => 'month',
    ];

    protected function casts(): array
    {
        return [
            'ticket_preferences' => 'array',
            'show_signatures' => 'boolean',
            'show_commission' => 'boolean',
            'priority_days' => 'integer',
        ];
    }

    public static function current(): self
    {
        $tenantId = TenantScope::tenantId();
        abort_unless($tenantId !== null, 403, 'Un contexte tenant valide est obligatoire.');
        $settings = static::query()->where('tenant_id', $tenantId)->first();
        if ($settings !== null) {
            return $settings;
        }

        $platform = PlatformSetting::current();

        return (new static)->fill([
            'tenant_id' => $tenantId,
            'ticket_paper' => $platform->ticket_paper,
            'show_signatures' => $platform->show_signatures,
            'show_commission' => $platform->show_commission,
            'priority_days' => $platform->priority_days,
            'report_period' => $platform->report_period,
        ]);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
