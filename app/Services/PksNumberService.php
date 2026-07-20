<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\CarbonInterface;

class PksNumberService
{
    public const BASELINE_NUMBER = 6298;

    public function next(CarbonInterface $date): string
    {
        $lastNumber = Employee::query()
            ->whereNotNull('no_pks_masuk')
            ->lockForUpdate()
            ->pluck('no_pks_masuk')
            ->reduce(function (int $highest, string $pks): int {
                if (preg_match('/^(\d+)\s*\/\s*HR-ESM\//i', trim($pks), $match)) {
                    return max($highest, (int) $match[1]);
                }

                return $highest;
            }, self::BASELINE_NUMBER);

        return $this->format($lastNumber + 1, $date);
    }

    public function format(int $sequence, CarbonInterface $date): string
    {
        return sprintf(
            '%d/HR-ESM/%s/%s',
            $sequence,
            $this->romanMonth((int) $date->format('n')),
            $date->format('y')
        );
    }

    private function romanMonth(int $month): string
    {
        return [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][$month];
    }
}
