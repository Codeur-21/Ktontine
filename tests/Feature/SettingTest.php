<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_settings_use_central_document_defaults_without_copying_identity_or_writing_rows(): void
    {
        PlatformSetting::current()->fill(['contact_email' => 'central@example.com', 'address' => 'Central', 'ticket_paper' => 'A5', 'priority_days' => 3, 'show_commission' => false])->save();
        $owner = $this->owner('alpha');
        $settings = Setting::current();
        $this->assertFalse($settings->exists);
        $this->assertSame($owner->tenant_id, $settings->tenant_id);
        $this->assertSame('A5', $settings->ticket_paper);
        $this->assertSame(3, $settings->priority_days);
        $this->assertFalse($settings->show_commission);
        $this->assertSame('', $settings->contact_email);
        $this->assertSame('', $settings->address);
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_saving_and_loading_current_settings_remains_isolated_by_tenant(): void
    {
        $alpha = $this->owner('alpha');
        Setting::current()->fill(['contact_email' => 'alpha@example.com', 'ticket_preferences' => ['color' => 'blue'], 'show_signatures' => false])->save();
        $this->owner('beta');
        $this->assertSame('', Setting::current()->contact_email);
        Setting::current()->fill(['contact_email' => 'beta@example.com'])->save();
        $this->actingAs($alpha);
        $settings = Setting::current();
        $this->assertSame('alpha@example.com', $settings->contact_email);
        $this->assertSame(['color' => 'blue'], $settings->ticket_preferences);
        $this->assertFalse($settings->show_signatures);
        $this->assertDatabaseCount('settings', 2);
    }

    public function test_current_settings_require_a_valid_tenant_context(): void
    {
        $this->expectException(HttpException::class);
        Setting::current();
    }

    public function test_suspended_tenant_cannot_access_current_settings(): void
    {
        $owner = $this->owner('alpha');
        $owner->tenant()->update(['is_active' => false]);
        $this->expectException(HttpException::class);
        Setting::current();
    }

    private function owner(string $slug): User
    {
        $tenant = Tenant::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $owner = UserFactory::new()->create(['tenant_id' => $tenant->id, 'role' => 'owner', 'is_active' => true]);
        $this->actingAs($owner);

        return $owner;
    }
}
