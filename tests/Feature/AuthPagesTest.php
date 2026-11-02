<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    public function test_login_page_displays_the_form_and_registration_link(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSeeText('Vous découvrez Ktontine ?')
            ->assertSee('href="'.route('register').'"', false)
            ->assertSee('type="password"', false);
    }

    public function test_registration_page_displays_both_password_fields_without_raw_blade_expressions(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertSeeText('Créer votre espace')
            ->assertSee('id="password" type="password"', false)
            ->assertSee('id="password_confirmation" type="password"', false)
            ->assertDontSee('aria-invalid="{ $errors', false)
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_password_reset_page_uses_the_shared_authentication_layout(): void
    {
        $response = $this->get('/reset_password');

        $response->assertOk()
            ->assertSeeText('Retrouvez votre accès en quelques étapes.')
            ->assertSee('ktontine-auth.css', false);
    }
}
