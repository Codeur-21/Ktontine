<?php

return [
    'title' => 'Vos choix, notre confiance.',
    'intro' => 'Ktontine utilise des cookies pour maintenir votre connexion, sécuriser vos formulaires et mémoriser vos choix. Actuellement, seuls ces cookies nécessaires sont déclarés.',
    'link' => 'Consultez notre <a href=":url">politique d\'utilisation des cookies</a> pour en savoir plus.',

    'essentials' => 'Essentiels uniquement',
    'all' => 'Tout accepter',
    'customize' => 'Personnaliser mes choix',
    'manage' => 'Gérer les cookies',
    'details' => [
        'more' => 'Plus d\'informations',
        'less' => 'Moins d\'informations',
    ],
    'save' => 'Enregistrer mes choix',
    'cookie' => 'Cookie',
    'purpose' => 'But',
    'duration' => 'Durée',
    'year' => 'Année|Années',
    'day' => 'Jour|Jours',
    'hour' => 'Heure|Heures',
    'minute' => 'Minute|Minutes',

    'categories' => [
        'essentials' => [
            'title' => 'Cookies de fonctionnement',
            'description' => 'Nous devons utiliser certains cookies pour pouvoir faire fonctionner certaines pages web. C\'est la raison pour laquelle ils ne nécessitent pas votre consentement.',
        ],
        'analytics' => [
            'title' => 'Cookies analytiques',
            'description' => 'Nous utilisons ces cookies uniquement à des fins de recherche interne sur la manière dont nous pouvons améliorer le service que nous offrons à tous nos utilisateurs. Ces cookies permettent d\'évaluer la manière dont vous interagissez avec notre site web.',
        ],
        'optional' => [
            'title' => 'Cookies optionnels',
            'description' => 'Ces cookies activent des fonctionnalités susceptibles d\'améliorer votre expérience utilisateur, mais leur absence n\'a pas d\'incidence sur votre capacité à naviguer sur notre site Web.',
        ],
    ],

    'defaults' => [
        'consent' => 'Stockage des préférences de consentement aux cookies de l\'utilisateur.',
        'session' => 'Identification de la session de navigation de l\'utilisateur.',
        'csrf' => 'Protection de l\'utilisateur et notre site contre les attaques d\'usurpation d\'identité lors des requêtes.',
        '_ga' => 'Cookie principal utilisé par Google Analytics, permettant de distinguer un visiteur d\'un autre.',
        '_ga_ID' => 'Utilisé par Google Analytics pour conserver l\'état de la session.',
        '_gid' => 'Utilisé par Google Analytics pour identifier un visiteur.',
        '_gat' => 'Utilisé par Google Analytics pour limiter le taux de demande.',
    ],
];
