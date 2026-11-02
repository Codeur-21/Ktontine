<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Bienvenue sur Ktontine</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; }
            .email-content { padding: 28px 22px !important; }
            .email-heading { font-size: 28px !important; line-height: 36px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; color:#26201c; font-family:Arial,Helvetica,sans-serif; -webkit-text-size-adjust:100%;">
    <div style="display:none; font-size:1px; line-height:1px; color:#f3f4f6; max-height:0; max-width:0; opacity:0; overflow:hidden; mso-hide:all;">Bienvenue {{ $name }} ! Votre espace {{ $businessName }} est prêt. Découvrez vos premiers pas sur Ktontine.</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f4f6;">
        <tr>
            <td align="center" style="padding:32px 12px;">
                <!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
                <table role="presentation" class="email-container" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; background-color:#ffffff; border:1px solid #e7e5e4; border-radius:20px;">
                    <tr>
                        <td class="email-content" style="padding:30px 40px; background-color:#191613; border-radius:20px 20px 0 0; border-bottom:4px solid #f97316;">
                            <span style="font-size:27px; font-weight:bold; letter-spacing:-1px; color:#ffffff;">K<span style="color:#fb923c;">tontine</span></span>
                            <p style="margin:10px 0 0; font-size:13px; line-height:20px; color:#d6d3d1;">Vos tontines, en toute simplicité.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-content" style="padding:36px 40px 28px;">
                            <p style="margin:0 0 16px; font-size:12px; line-height:18px; font-weight:bold; letter-spacing:2px; color:#c2410c;">BIENVENUE DANS VOTRE ESPACE</p>
                            <h1 class="email-heading" style="margin:0 0 20px; font-size:32px; line-height:40px; letter-spacing:-1px; color:#191613;">Tout commence ici,<br>{{ $name }}.</h1>
                            <p style="margin:0 0 24px; font-size:16px; line-height:27px; color:#57534e;">Merci d’avoir choisi Ktontine. Votre compte a été créé : vous pouvez maintenant vous connecter et commencer à organiser votre activité.</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fff7ed; border:1px solid #fed7aa; border-radius:12px;">
                                <tr><td style="padding:20px 22px;">
                                    <p style="margin:0 0 7px; font-size:12px; line-height:18px; font-weight:bold; color:#9a3412;">VOTRE ESPACE DE TONTINE</p>
                                    <p style="margin:0; font-size:20px; line-height:28px; font-weight:bold; color:#292524; overflow-wrap:anywhere;">{{ $businessName }}</p>
                                </td></tr>
                            </table>
                            <h2 style="margin:30px 0 18px; font-size:19px; line-height:27px; color:#191613;">Vos premiers pas</h2>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td valign="top" width="36" style="padding:0 0 18px; font-size:18px; line-height:25px; font-weight:bold; color:#c2410c;">01</td>
                                    <td style="padding:0 0 18px; font-size:14px; line-height:23px; color:#57534e;"><strong style="color:#292524;">Ajoutez vos clients.</strong><br>Retrouvez leurs coordonnées au même endroit.</td>
                                </tr>
                                <tr>
                                    <td valign="top" width="36" style="padding:0 0 18px; font-size:18px; line-height:25px; font-weight:bold; color:#c2410c;">02</td>
                                    <td style="padding:0 0 18px; font-size:14px; line-height:23px; color:#57534e;"><strong style="color:#292524;">Créez votre première tontine.</strong><br>Choisissez le client, le montant et les dates.</td>
                                </tr>
                                <tr>
                                    <td valign="top" width="36" style="padding:0 0 6px; font-size:18px; line-height:25px; font-weight:bold; color:#c2410c;">03</td>
                                    <td style="padding:0 0 6px; font-size:14px; line-height:23px; color:#57534e;"><strong style="color:#292524;">Suivez vos cotisations.</strong><br>Enregistrez les versements et consultez vos rapports.</td>
                                </tr>
                            </table>
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:28px 0 22px;">
                                <tr><td align="center" bgcolor="#c2410c" style="border-radius:10px; mso-padding-alt:16px 26px;">
                                    <a href="{{ $loginUrl }}" style="display:inline-block; padding:16px 26px; border:1px solid #c2410c; border-radius:10px; background-color:#c2410c; color:#ffffff; font-size:16px; line-height:22px; font-weight:bold; text-decoration:none;">Me connecter à mon espace</a>
                                </td></tr>
                            </table>
                            <p style="margin:0; font-size:15px; line-height:25px; color:#57534e;">À très bientôt,<br><strong style="color:#292524;">L’équipe Ktontine</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-content" style="padding:22px 40px; border-top:1px solid #eee8e2; background-color:#fafaf9; border-radius:0 0 20px 20px;">
                            <p style="margin:0; font-size:12px; line-height:20px; color:#78716c;">Vous recevez ce message à la suite de la création de votre compte sur Ktontine. Si vous n’êtes pas à l’origine de cette inscription, vous pouvez ignorer ce message.</p>
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
