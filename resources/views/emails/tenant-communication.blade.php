<!DOCTYPE html>
<html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{{ $platformName }}</title></head>
<body style="margin:0;background:#f3f4f6;color:#302318;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:28px 12px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;border:1px solid #e7e5e4;border-radius:18px;background:white;">
<tr><td style="padding:28px;background:#191613;border-radius:18px 18px 0 0;border-bottom:4px solid #f97316;"><strong style="font-size:26px;color:#fff;">{{ $platformName }}</strong><p style="margin:10px 0 0;font-size:13px;color:#d6c7bc;">Un message pour votre espace {{ $businessName }}</p></td></tr>
<tr><td style="padding:30px 28px;">
@foreach (preg_split('/\R\s*\R/u', $personalMessage) as $paragraph)
<p style="margin:0 0 18px;font-size:15px;line-height:27px;color:#57534e;white-space:pre-line;overflow-wrap:anywhere;">{{ $paragraph }}</p>
@endforeach
@if ($buttonUrl !== '' && $buttonLabel !== '')<table role="presentation" cellpadding="0" cellspacing="0" style="margin:26px 0;"><tr><td style="border-radius:10px;background:#c2410c;"><a href="{{ $buttonUrl }}" style="display:inline-block;padding:15px 24px;border:1px solid #c2410c;border-radius:10px;background:#c2410c;color:#fff;font-size:15px;font-weight:bold;text-decoration:none;">{{ $buttonLabel }}</a></td></tr></table>@endif
<p style="margin:24px 0 0;font-size:14px;line-height:24px;color:#57534e;">À bientôt,<br><strong>L’équipe {{ $platformName }}</strong></p>
</td></tr>
<tr><td style="padding:20px 28px;background:#fafaf9;border-top:1px solid #eee8e2;border-radius:0 0 18px 18px;font-size:12px;line-height:20px;color:#78716c;">Message adressé à {{ $name }}, propriétaire de l’espace {{ $businessName }}. Pour toute question, contactez le support depuis l’application.</td></tr>
</table></td></tr></table></body></html>
