<?php

namespace Tests\Unit;

use App\Services\PksNumberService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PksNumberServiceTest extends TestCase
{
    public function test_it_formats_the_next_known_pks_number(): void
    {
        $service = new PksNumberService();

        $this->assertSame(
            '6299/HR-ESM/VII/26',
            $service->format(6299, Carbon::parse('2026-07-20'))
        );
    }
}
