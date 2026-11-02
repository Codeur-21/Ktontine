<?php

namespace Tests\Feature;

use App\Mail\SubscriptionExpired;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class SubscriptionExpiredMailTest extends TestCase
{
    public function test_trial_expiration_mail_has_personalized_details_and_payment_button(): void
    {
        $mail = new SubscriptionExpired('Abdoulaye', 'Mon espace', CarbonImmutable::parse('2026-10-30'), 'https://example.com/abonnement', isTrial: true);
        $mail->assertHasSubject('Votre essai gratuit de 30 jours est terminé — Ktontine');
        $mail->assertSeeInHtml('Abdoulaye');
        $mail->assertSeeInHtml('Mon espace');
        $mail->assertSeeInHtml('30/10/2026');
        $mail->assertSeeInHtml('200 000 GNF / an');
        $mail->assertSeeInHtml('https://example.com/abonnement');
        $mail->assertSeeInText('Votre période d’essai gratuit de 30 jours a pris fin.');
        $mail->assertDontSeeInHtml('Si le bouton ne fonctionne pas');
    }

    public function test_subscription_expiration_mail_uses_renewal_wording_and_escapes_names(): void
    {
        $mail = new SubscriptionExpired('<script>alert(1)</script>', 'Mon espace', CarbonImmutable::parse('2026-10-30'), 'https://example.com/abonnement');
        $mail->assertHasSubject('Votre abonnement a expiré — Ktontine');
        $mail->assertSeeInHtml('&lt;script&gt;alert(1)&lt;/script&gt;', escape: false);
        $mail->assertDontSeeInHtml('<script>alert(1)</script>', escape: false);
        $mail->assertSeeInHtml('Renouveler mon abonnement');
        $mail->assertSeeInText('La période de validité de votre abonnement a pris fin.');
        $mail->assertDontSeeInHtml('30 jours');
    }
}
