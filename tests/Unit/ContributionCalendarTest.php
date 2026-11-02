<?php

namespace Tests\Unit;

use App\Models\Tontine;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContributionCalendarTest extends TestCase
{
    #[DataProvider('cycleDates')]
    public function test_cycle_has_thirty_contribution_days_including_saturday(string $start, string $end): void
    {
        $actual = Tontine::contributionEndDate($start);

        $this->assertSame($end, $actual->toDateString());
        $this->assertSame(30, Tontine::contributionDaysBetween(CarbonImmutable::parse($start), $actual));
        $this->assertFalse($actual->isSunday());
    }

    public static function cycleDates(): array
    {
        return [
            ['2026-11-02', '2026-12-05'],
            ['2026-11-01', '2026-12-05'],
            ['2028-02-01', '2028-03-06'],
            ['2026-12-31', '2027-02-03'],
        ];
    }

    public function test_sunday_alone_and_reversed_ranges_have_no_contributions(): void
    {
        $sunday = CarbonImmutable::parse('2026-11-01');

        $this->assertSame(0, Tontine::contributionDaysBetween($sunday, $sunday));
        $this->assertSame(0, Tontine::contributionDaysBetween($sunday->addDay(), $sunday));
        $this->assertSame(1, Tontine::contributionDaysBetween($sunday->subDay(), $sunday));
    }
}
