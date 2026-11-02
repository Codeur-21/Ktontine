<?php

namespace Tests\Feature;

use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    public function test_welcome_renders_branded_consent_actions_and_preferences_controls(): void
    {
        $this->app->setLocale('fr');
        $this->get('/')->assertOk()->assertSee('Vos choix, notre confiance.')
            ->assertSee('Essentiels uniquement')->assertSee('Tout accepter')
            ->assertSee('Personnaliser mes choix')->assertSee('Gérer mes cookies')
            ->assertSee('Toujours actifs')->assertSee('Enregistrer mes choix')
            ->assertSee(route('cookieconsent.accept.essentials'), false)
            ->assertSee(route('cookieconsent.accept.configuration'), false)
            ->assertSee(route('cookieconsent.reset'), false);
    }

    public function test_essential_choice_sets_consent_and_reset_returns_the_personalized_banner(): void
    {
        $this->app->setLocale('fr');
        $this->postJson(route('cookieconsent.accept.essentials'))
            ->assertOk()->assertCookie(config('cookieconsent.cookie.name'));
        $this->postJson(route('cookieconsent.reset'))->assertOk()->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['notice', 'scripts']);
    }
}
