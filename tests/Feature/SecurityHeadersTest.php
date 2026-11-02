<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_authentication_and_redirect_responses_have_security_headers(): void
    {
        foreach (['/', '/login', '/register', '/dashboard'] as $path) {
            $response = $this->get($path);
            $response->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()')
                ->assertHeaderMissing('X-Powered-By');
            $this->assertStringContainsString("object-src 'none'", $response->headers->get('Content-Security-Policy'));
            $this->assertStringContainsString("worker-src 'self' blob:", $response->headers->get('Content-Security-Policy'));
        }
    }

    public function test_error_and_json_responses_also_receive_protection(): void
    {
        Route::get('/security-test-forbidden', fn () => abort(403));
        Route::get('/security-test-json', fn () => response()->json(['ok' => true]));
        $this->get('/security-test-forbidden')->assertForbidden()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/does-not-exist')->assertNotFound()->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertSee('404 · PAGE INTROUVABLE')->assertSee('ktontine-mascotte-v1.png')
            ->assertSee('Retour à l’accueil')->assertSee(route('login'));
        $this->getJson('/does-not-exist')->assertNotFound()->assertJsonStructure(['message'])
            ->assertDontSee('ktontine-mascotte-v1.png');
        $this->getJson('/security-test-forbidden')->assertForbidden()->assertHeader('Content-Security-Policy');
        $this->getJson('/security-test-json')->assertOk()->assertJson(['ok' => true])->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_hsts_and_mixed_content_upgrade_are_limited_to_production_https(): void
    {
        $this->app->instance('env', 'production');
        $response = $this->get('https://localhost/login');
        $response->assertOk()->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $policy = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('upgrade-insecure-requests', $policy);
        $this->assertStringNotContainsString(' ws:', $policy);
        $this->assertStringNotContainsString(' http:', $policy);
        $this->get('http://localhost/login')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_local_https_does_not_pin_localhost_and_allows_vite_connections(): void
    {
        $this->app->instance('env', 'local');
        $response = $this->get('https://localhost/login');
        $response->assertHeaderMissing('Strict-Transport-Security');
        $policy = $response->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString('upgrade-insecure-requests', $policy);
        $this->assertStringContainsString("connect-src 'self' http: https: ws: wss:", $policy);
    }
}
