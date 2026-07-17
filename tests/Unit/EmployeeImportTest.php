<?php

namespace Tests\Unit;

use App\Http\Controllers\EmployeeController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class EmployeeImportTest extends TestCase
{
    #[DataProvider('genderValues')]
    public function test_import_normalizes_gender_values(string $input, string $expected): void
    {
        $data = $this->invokePrivate('buildRowData', [
            '3173080101000011',
            '3173080101000011',
            ['Nama Karyawan', $input],
            ['nama_ktp' => 0, 'jenis_kelamin' => 1],
        ]);

        $this->assertSame($expected, $data['jenis_kelamin']);
    }

    #[DataProvider('statusValues')]
    public function test_import_normalizes_status_values(string $input, string $expected): void
    {
        $data = $this->invokePrivate('buildRowData', [
            '3173080101000011',
            '3173080101000011',
            ['Nama Karyawan', $input],
            ['nama_ktp' => 0, 'status' => 1],
        ]);

        $this->assertSame($expected, $data['status']);
    }

    public static function statusValues(): array
    {
        return [
            'uppercase aktif' => ['AKTIF', 'Aktif'],
            'lowercase aktif' => ['aktif', 'Aktif'],
            'tidak aktif' => ['Tidak Aktif', 'Resign'],
            'non aktif' => ['NON AKTIF', 'Resign'],
            'lowercase resign' => ['resign', 'Resign'],
            'batal' => ['Batal', 'Cancel'],
        ];
    }

    public function test_import_rejects_unknown_status(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Status kerja tidak valid: 'Selesai'");

        $this->invokePrivate('buildRowData', [
            '3173080101000011',
            '3173080101000011',
            ['Nama Karyawan', 'Selesai'],
            ['nama_ktp' => 0, 'status' => 1],
        ]);
    }

    public static function genderValues(): array
    {
        return [
            'laki-laki' => ['LAKI-LAKI', 'Pria'],
            'laki with spaces' => ['Laki - Laki', 'Pria'],
            'perempuan' => ['PEREMPUAN', 'Wanita'],
            'existing pria' => ['Pria', 'Pria'],
            'existing wanita' => ['Wanita', 'Wanita'],
        ];
    }

    public function test_import_requires_nik_and_name_headers(): void
    {
        $this->assertNull($this->invokePrivate('detectColumnMapping', [['NIK KTP']]));
        $this->assertNull($this->invokePrivate('detectColumnMapping', [['NAMA KTP']]));

        $mapping = $this->invokePrivate('detectColumnMapping', [['NO', 'NIK KTP', 'NAMA KTP']]);

        $this->assertSame(1, $mapping['nik_ktp']);
        $this->assertSame(2, $mapping['nama_ktp']);
    }

    public function test_import_requires_name_value_on_each_row(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nama sesuai KTP wajib diisi');

        $this->invokePrivate('extractImportIdentity', [
            ['3173080101000011', ''],
            ['nik_ktp' => 0, 'nama_ktp' => 1],
        ]);
    }

    public function test_partial_import_ignores_empty_optional_fields(): void
    {
        $filtered = $this->invokePrivate('filterImportData', [[
            'nik' => '3173080101000011',
            'nik_ktp' => '3173080101000011',
            'nama_lengkap' => 'Budi',
            'nama_ktp' => 'Budi',
            'status' => '',
            'jenis_kelamin' => '',
            'jumlah_anak' => '0',
        ]]);

        $this->assertSame([
            'nik' => '3173080101000011',
            'nik_ktp' => '3173080101000011',
            'nama_lengkap' => 'Budi',
            'nama_ktp' => 'Budi',
            'jumlah_anak' => '0',
        ], $filtered);
    }

    public function test_row_is_not_empty_when_sequence_number_is_blank(): void
    {
        $this->assertFalse($this->invokePrivate('isImportRowEmpty', [['', '3173080101000011', 'Budi']]));
        $this->assertTrue($this->invokePrivate('isImportRowEmpty', [['', ' ', null]]));
    }

    public function test_import_rejects_duplicate_nik_in_the_same_file(): void
    {
        $seenNiks = [];
        $arguments = ['3173080101000011', 2, &$seenNiks];
        $this->invokePrivate('ensureUniqueImportIdentity', $arguments);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('NIK KTP duplikat dengan baris 2');

        $arguments = ['3173080101000011', 8, &$seenNiks];
        $this->invokePrivate('ensureUniqueImportIdentity', $arguments);
    }

    #[DataProvider('importDateValues')]
    public function test_import_normalizes_supported_date_values(string $input, string $expected): void
    {
        $data = $this->invokePrivate('buildRowData', [
            '3173080101000011',
            '3173080101000011',
            ['Nama Karyawan', $input],
            ['nama_ktp' => 0, 'tanggal_lahir' => 1],
        ]);

        $this->assertSame($expected, $data['tanggal_lahir']);
    }

    public static function importDateValues(): array
    {
        return [
            'bulan Januari' => ['01 JANUARI 2000', '2000-01-01'],
            'bulan Desember' => ['31 DESEMBER 1985', '1985-12-31'],
            'bulan Februari' => ['09 FEBRUARI 1977', '1977-02-09'],
            'format garis miring' => ['9/2/1977', '1977-02-09'],
            'format database' => ['1977-02-09', '1977-02-09'],
            'serial Excel' => ['36526', '2000-01-01'],
        ];
    }

    public function test_import_rejects_an_invalid_calendar_date(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Format tanggal tidak dikenali pada TANGGAL LAHIR');

        $this->invokePrivate('buildRowData', [
            '3173080101000011',
            '3173080101000011',
            ['Nama Karyawan', '31 FEBRUARI 2000'],
            ['nama_ktp' => 0, 'tanggal_lahir' => 1],
        ]);
    }

    private function invokePrivate(string $method, array $arguments): mixed
    {
        $controller = new EmployeeController;
        $reflection = new ReflectionMethod($controller, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($controller, $arguments);
    }
}
