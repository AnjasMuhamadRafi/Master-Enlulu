<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpuTk extends Model
{
    protected $table = 'bpu_tk';

    protected $fillable = [
        'nomor_identitas',
        'nama_lengkap',
        'tanggal_lahir',
        'handphone',
        'email',
        'jenis_pekerjaan_1',
        'jenis_pekerjaan_2',
        'lokasi_pekerjaan',
        'upah',
        'kode_paket',
        'bulan_iuran',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'upah' => 'integer',
        'bulan_iuran' => 'integer',
    ];

    public function isLinkedToMaster(): bool
    {
        return Employee::where('nik', $this->nomor_identitas)
            ->orWhere('nik_ktp', $this->nomor_identitas)
            ->exists();
    }

    public function isComplete(): bool
    {
        return !empty($this->nomor_identitas)
            && !empty($this->nama_lengkap)
            && !empty($this->tanggal_lahir)
            && !empty($this->handphone)
            && !empty($this->email)
            && !empty($this->jenis_pekerjaan_1)
            && !empty($this->lokasi_pekerjaan)
            && !empty($this->upah)
            && !empty($this->kode_paket)
            && !empty($this->bulan_iuran);
    }
}
