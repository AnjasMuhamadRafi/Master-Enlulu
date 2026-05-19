<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Set NIK as primary key instead of id
    protected $primaryKey = 'nik';
    
    // NIK is not auto-incrementing
    public $incrementing = false;
    
    // NIK is a string, not int
    protected $keyType = 'string';

    protected $fillable = [
        'nik',
        'nik_ktp',
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
    ];

    protected $casts = [];

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
