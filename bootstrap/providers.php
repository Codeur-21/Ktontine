<?php

use App\Providers\AppServiceProvider;
use App\Providers\CookiesServiceProvider;
use App\Providers\Filament\WorkspacePanelProvider;
use EragLaravelPwa\EragLaravelPwaServiceProvider;

return [
    AppServiceProvider::class,
    WorkspacePanelProvider::class,
    EragLaravelPwaServiceProvider::class,

    AppServiceProvider::class,
    CookiesServiceProvider::class,
];
