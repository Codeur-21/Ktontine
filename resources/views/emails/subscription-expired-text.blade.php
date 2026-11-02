{{ $isTrial ? 'Votre essai gratuit de 30 jours est terminé.' : 'Votre abonnement Ktontine a expiré.' }}

Bonjour {{ $name }},

{{ $isTrial ? 'Votre période d’essai gratuit de 30 jours a pris fin.' : 'La période de validité de votre abonnement a pris fin.' }}
Espace : {{ $businessName }}
Date d’expiration : {{ $expiredAt->format('d/m/Y') }}
Abonnement annuel : 200 000 GNF / an.

Consultez les modalités {{ $isTrial ? 'd’abonnement' : 'de renouvellement' }} dans votre espace :
{{ $paymentUrl }}

Si vous avez déjà effectué les démarches de renouvellement, vérifiez leur prise en compte dans votre espace ou contactez le support depuis l’application.

L’équipe Ktontine
Ce message concerne la validité de votre accès à Ktontine pour l’espace {{ $businessName }}.
