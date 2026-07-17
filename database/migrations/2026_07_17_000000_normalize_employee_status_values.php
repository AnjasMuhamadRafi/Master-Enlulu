<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $statusAliases = [
            'Aktif' => ['AKTIF', 'ACTIVE'],
            'Training' => ['TRAINING', 'PELATIHAN'],
            'Resign' => ['RESIGN', 'RESIGNED', 'TIDAK AKTIF', 'TIDAKAKTIF', 'NON AKTIF', 'NONAKTIF'],
            'Cancel' => ['CANCEL', 'CANCELED', 'CANCELLED', 'BATAL'],
            'Fraud' => ['FRAUD', 'KECURANGAN'],
        ];

        foreach ($statusAliases as $canonical => $aliases) {
            $placeholders = implode(',', array_fill(0, count($aliases), '?'));

            DB::table('employees')
                ->whereRaw("UPPER(TRIM(status)) IN ($placeholders)", $aliases)
                ->update(['status' => $canonical]);
        }
    }

    public function down(): void
    {
        // Kapitalisasi dan alias lama tidak dapat direkonstruksi secara akurat.
    }
};
