<?php

namespace App\Console\Commands;

use App\PlatformNotifications;
use Illuminate\Console\Command;

class NotifyPlatformExpirations extends Command
{
    protected $signature = 'platform:notify-expirations';

    protected $description = 'Notifier les administrateurs des essais et abonnements proches de leur fin ou expirés.';

    public function handle(PlatformNotifications $notifications): int
    {
        $count = $notifications->checkExpirations();
        $this->info($count.' nouvelle(s) alerte(s) enregistrée(s).');

        return self::SUCCESS;
    }
}
