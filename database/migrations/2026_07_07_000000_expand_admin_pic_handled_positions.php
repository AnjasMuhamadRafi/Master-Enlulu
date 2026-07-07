<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentMap = config('positions.admin_pic_departments', []);

        DB::table('users')
            ->whereNotNull('handled_position')
            ->orderBy('id')
            ->get()
            ->each(function ($user) use ($departmentMap) {
                $handledPositions = json_decode($user->handled_position, true);

                if (!is_array($handledPositions)) {
                    return;
                }

                $expanded = collect($handledPositions)
                    ->map(fn ($position) => trim((string) $position))
                    ->filter()
                    ->flatMap(function ($position) use ($departmentMap) {
                        if (str_starts_with(strtolower($position), 'position:')) {
                            return [substr($position, strlen('position:'))];
                        }

                        $position = strtoupper($position);

                        return $departmentMap[$position] ?? [$position];
                    })
                    ->map(fn ($position) => trim(strtoupper((string) $position)))
                    ->filter()
                    ->unique()
                    ->map(fn ($position) => 'position:' . $position)
                    ->values()
                    ->all();

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'handled_position' => empty($expanded) ? null : json_encode($expanded),
                    ]);
            });
    }

    public function down(): void
    {
        // Tidak dikembalikan ke format kelompok karena posisi baru bersifat dinamis.
    }
};
