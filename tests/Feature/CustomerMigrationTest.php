<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_tracking_tables_can_be_recreated_in_dependency_order(): void
    {
        $tontines = require database_path('migrations/2026_10_19_000000_create_tontines_table.php');
        $days = require database_path('migrations/2026_10_19_000001_create_tontine_days_table.php');
        $payments = require database_path('migrations/2026_10_19_000002_create_tontine_payments_table.php');
        $contributions = require database_path('migrations/2026_10_20_170642_create_contributions_table.php');
        $contributions->down();
        $payments->down();
        $days->down();
        $tontines->down();
        $tontines->up();
        $days->up();
        $payments->up();
        $contributions->up();

        $this->assertFalse(Schema::hasTable('clients'));
        $this->assertFalse(Schema::hasColumn('tontines', 'client_id'));
        $this->assertTrue(Schema::hasColumn('tontines', 'customer_id'));
        $this->assertTrue(Schema::hasColumn('tontine_days', 'tenant_id'));
        $this->assertTrue(Schema::hasColumn('tontine_payments', 'tenant_id'));
        $this->assertTrue(Schema::hasColumn('tontine_payments', 'history'));
    }
}
