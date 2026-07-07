<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Konversi data string lama ke JSON array sebelum ubah tipe kolom
        DB::table('users')->whereNotNull('handled_position')->get()->each(function ($user) {
            $current = $user->handled_position;
            // Jika belum berformat JSON array, konversi ke array
            if ($current && $current[0] !== '[') {
                DB::table('users')->where('id', $user->id)->update([
                    'handled_position' => json_encode([$current]),
                ]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('handled_position')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Konversi kembali JSON array ke string (ambil elemen pertama)
        DB::table('users')->whereNotNull('handled_position')->get()->each(function ($user) {
            $arr = json_decode($user->handled_position, true);
            DB::table('users')->where('id', $user->id)->update([
                'handled_position' => is_array($arr) ? ($arr[0] ?? null) : null,
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('handled_position', 100)->nullable()->change();
        });
    }
};
