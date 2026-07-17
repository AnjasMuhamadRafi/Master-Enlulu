<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    public const EMPLOYMENT_STATUSES = ['Aktif', 'Training', 'Resign', 'Cancel', 'Fraud'];

    // Set NIK as primary key instead of id
    protected $primaryKey = 'nik';
    
    // NIK is not auto-incrementing
    public $incrementing = false;
    
    // NIK is a string, not int
    protected $keyType = 'string';

    protected $fillable = [
        'nik',
        'nik_ktp',
        'nik_enlulu',
        'nik_os',
        'nama_ktp',
        'nama_lengkap',
        'kode_vendor',
        'posisi',
        'penempatan',
        'type_lokasi',
        'area_kerja',
        'no_rekening',
        'nama_bank',
        'nama_di_rekening',
        'status',
        'note1',

        // A. Biodata
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'pendidikan',
        'status_pernikahan',
        'jumlah_anak',
        'alamat',
        'kelurahan',
        'kecamatan',
        'kota',
        'propinsi',
        'status_tempat_tinggal',
        'no_hp',
        'no_kk',
        'email',
        'no_bpjs_tk',
        'no_bpjs_kesehatan',
        'keterangan_biodata',

        // B. Emergency Contact
        'ec_nama',
        'ec_alamat',
        'ec_no_hp',
        'ec_hubungan',

        // D. Foto Wajah
        'foto',

        // E. Penempatan Kerja
        'nama_customer',
        'tanggal_masuk',
        'tanggal_keluar',
        'tanggal_perpanjangan_terakhir',
        'keterangan_perpanjangan',
        'no_pks_masuk',
        'no_pks_perpanjangan',
        'nama_perekrut',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
        'tanggal_perpanjangan_terakhir' => 'date',
        'jumlah_anak' => 'integer',
    ];

    public static function normalizeStatusValue(?string $status): ?string
    {
        $value = trim((string) $status);
        if ($value === '') {
            return null;
        }

        $key = preg_replace('/[^A-Z]/', '', strtoupper($value));

        return match ($key) {
            'AKTIF', 'ACTIVE' => 'Aktif',
            'TRAINING', 'PELATIHAN' => 'Training',
            'RESIGN', 'RESIGNED', 'TIDAKAKTIF', 'NONAKTIF' => 'Resign',
            'CANCEL', 'CANCELED', 'CANCELLED', 'BATAL' => 'Cancel',
            'FRAUD', 'KECURANGAN' => 'Fraud',
            default => null,
        };
    }

    public function setStatusAttribute(?string $status): void
    {
        $normalized = self::normalizeStatusValue($status);

        if ($normalized === null && trim((string) $status) !== '') {
            throw new \InvalidArgumentException("Status kerja tidak valid: '$status'");
        }

        $this->attributes['status'] = $normalized;
    }

    /**
     * Get list of positions managed by this ADMIN/PIC employee
     * 
     * @return array List of positions managed, or empty array if not ADMIN/PIC
     */
    public function getManagedPositions(): array
    {
        if (!$this->posisi || !str_contains($this->posisi, 'ADMIN/PIC-')) {
            return [];
        }

        $adminPicDepartments = config('positions.admin_pic_departments', []);
        
        // Extract department name from posisi (e.g., 'ADMIN/PIC-SPRINTER' -> 'SPRINTER')
        $department = str_replace('ADMIN/PIC-', '', $this->posisi);
        
        return $adminPicDepartments[$department] ?? [];
    }

    /**
     * Check if this employee is an ADMIN/PIC
     * 
     * @return bool
     */
    public function isAdminPic(): bool
    {
        return $this->posisi && str_contains($this->posisi, 'ADMIN/PIC-');
    }
}
