@extends('layouts.app')

@section('title', 'Detail Karyawan')
@section('page-title', 'Detail Karyawan')

@php
    $val = fn ($v) => ($v === null || $v === '') ? '-' : $v;
    $tgl = fn ($d) => $d ? $d->format('d M Y') : '-';
@endphp

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 text-dark"><i class="bi bi-person-vcard" style="color: #FF6B35; margin-right: 8px;"></i> {{ $employee->nama_lengkap }}</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('employee.edit', $employee) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
            <a href="{{ route('employee.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="card text-center">
            <div class="card-body">
                @if ($employee->foto)
                    <img src="{{ asset('storage/' . $employee->foto) }}" alt="Foto Wajah" class="img-fluid rounded mb-3" style="max-height: 220px; object-fit: cover;">
                @else
                    <div class="d-flex align-items-center justify-content-center bg-light rounded mb-3" style="height: 200px;">
                        <i class="bi bi-person-bounding-box" style="font-size: 4rem; color: #ccc;"></i>
                    </div>
                @endif
                <h5 class="mb-1">{{ $employee->nama_lengkap }}</h5>
                <div class="text-muted small">{{ $val($employee->posisi) }}</div>
                <span class="badge bg-{{ $employee->status === 'Aktif' ? 'success' : ($employee->status === 'Resign' ? 'secondary' : 'warning') }} mt-2">{{ $val($employee->status) }}</span>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        {{-- A. BIODATA --}}
        <div class="card mb-4">
            <div class="card-header bg-light"><strong>A. Biodata</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><dl class="row mb-0">
                        <dt class="col-sm-5">NIK KTP</dt><dd class="col-sm-7">{{ $val($employee->nik_ktp) }}</dd>
                        <dt class="col-sm-5">Nama Sesuai KTP</dt><dd class="col-sm-7">{{ $val($employee->nama_ktp) }}</dd>
                        <dt class="col-sm-5">NIK ENLULU</dt><dd class="col-sm-7">{{ $val($employee->nik_enlulu) }}</dd>
                        <dt class="col-sm-5">NIK OS</dt><dd class="col-sm-7">{{ $val($employee->nik_os) }}</dd>
                        <dt class="col-sm-5">Tempat Lahir</dt><dd class="col-sm-7">{{ $val($employee->tempat_lahir) }}</dd>
                        <dt class="col-sm-5">Tanggal Lahir</dt><dd class="col-sm-7">{{ $tgl($employee->tanggal_lahir) }}</dd>
                        <dt class="col-sm-5">Jenis Kelamin</dt><dd class="col-sm-7">{{ $val($employee->jenis_kelamin) }}</dd>
                        <dt class="col-sm-5">Agama</dt><dd class="col-sm-7">{{ $val($employee->agama) }}</dd>
                        <dt class="col-sm-5">Pendidikan</dt><dd class="col-sm-7">{{ $val($employee->pendidikan) }}</dd>
                        <dt class="col-sm-5">Status Pernikahan</dt><dd class="col-sm-7">{{ $val($employee->status_pernikahan) }}</dd>
                        <dt class="col-sm-5">Jumlah Anak</dt><dd class="col-sm-7">{{ $employee->jumlah_anak !== null ? $employee->jumlah_anak . ' orang' : '-' }}</dd>
                    </dl></div>
                    <div class="col-md-6"><dl class="row mb-0">
                        <dt class="col-sm-5">Alamat Tinggal</dt><dd class="col-sm-7">{{ $val($employee->alamat) }}</dd>
                        <dt class="col-sm-5">Kelurahan</dt><dd class="col-sm-7">{{ $val($employee->kelurahan) }}</dd>
                        <dt class="col-sm-5">Kecamatan</dt><dd class="col-sm-7">{{ $val($employee->kecamatan) }}</dd>
                        <dt class="col-sm-5">Kota/Kabupaten</dt><dd class="col-sm-7">{{ $val($employee->kota) }}</dd>
                        <dt class="col-sm-5">Propinsi</dt><dd class="col-sm-7">{{ $val($employee->propinsi) }}</dd>
                        <dt class="col-sm-5">Status Tempat Tinggal</dt><dd class="col-sm-7">{{ $val($employee->status_tempat_tinggal) }}</dd>
                        <dt class="col-sm-5">No. HP</dt><dd class="col-sm-7">{{ $val($employee->no_hp) }}</dd>
                        <dt class="col-sm-5">No. KK</dt><dd class="col-sm-7">{{ $val($employee->no_kk) }}</dd>
                        <dt class="col-sm-5">E-mail</dt><dd class="col-sm-7">{{ $val($employee->email) }}</dd>
                    </dl></div>
                    <div class="col-12"><hr class="my-2"></div>
                    <div class="col-md-6"><dl class="row mb-0">
                        <dt class="col-sm-5">BPJS Ketenagakerjaan</dt><dd class="col-sm-7">{{ $val($employee->no_bpjs_tk) }}</dd>
                    </dl></div>
                    <div class="col-md-6"><dl class="row mb-0">
                        <dt class="col-sm-5">BPJS Kesehatan</dt><dd class="col-sm-7">{{ $val($employee->no_bpjs_kesehatan) }}</dd>
                    </dl></div>
                    <div class="col-12"><dl class="row mb-0">
                        <dt class="col-sm-2">Keterangan Biodata</dt><dd class="col-sm-10">{{ $val($employee->keterangan_biodata) }}</dd>
                    </dl></div>
                </div>
            </div>
        </div>

        {{-- B. EMERGENCY CONTACT --}}
        <div class="card mb-4">
            <div class="card-header bg-light"><strong>B. Emergency Contact (Tidak Serumah)</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><dl class="row mb-0">
                        <dt class="col-sm-5">Nama</dt><dd class="col-sm-7">{{ $val($employee->ec_nama) }}</dd>
                        <dt class="col-sm-5">No. HP</dt><dd class="col-sm-7">{{ $val($employee->ec_no_hp) }}</dd>
                    </dl></div>
                    <div class="col-md-6"><dl class="row mb-0">
                        <dt class="col-sm-5">Tempat Tinggal</dt><dd class="col-sm-7">{{ $val($employee->ec_alamat) }}</dd>
                        <dt class="col-sm-5">Hubungan</dt><dd class="col-sm-7">{{ $val($employee->ec_hubungan) }}</dd>
                    </dl></div>
                </div>
            </div>
        </div>

        {{-- C. BANKING --}}
        <div class="card mb-4">
            <div class="card-header bg-light"><strong>C. Banking</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><dl class="row mb-0">
                        <dt class="col-sm-5">Nama Bank</dt><dd class="col-sm-7">{{ $val($employee->nama_bank) }}</dd>
                        <dt class="col-sm-5">No. Rekening</dt><dd class="col-sm-7">{{ $val($employee->no_rekening) }}</dd>
                    </dl></div>
                    <div class="col-md-6"><dl class="row mb-0">
                        <dt class="col-sm-5">Nama Pemilik Rekening</dt><dd class="col-sm-7">{{ $val($employee->nama_di_rekening) }}</dd>
                    </dl></div>
                </div>
            </div>
        </div>

        {{-- E. PENEMPATAN KERJA --}}
        <div class="card mb-4">
            <div class="card-header bg-light"><strong>E. Penempatan Kerja</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><dl class="row mb-0">
                        <dt class="col-sm-5">Nama Customer</dt><dd class="col-sm-7">{{ $val($employee->nama_customer) }}</dd>
                        <dt class="col-sm-5">Kode Vendor</dt><dd class="col-sm-7">{{ $val($employee->kode_vendor) }}</dd>
                        <dt class="col-sm-5">Jabatan</dt><dd class="col-sm-7">{{ $val($employee->posisi) }}</dd>
                        <dt class="col-sm-5">Type Lokasi</dt><dd class="col-sm-7">{{ $val($employee->type_lokasi) }}</dd>
                        <dt class="col-sm-5">Lokasi Kerja</dt><dd class="col-sm-7">{{ $val($employee->penempatan) }}</dd>
                        <dt class="col-sm-5">Area Kerja</dt><dd class="col-sm-7">{{ $val($employee->area_kerja) }}</dd>
                        <dt class="col-sm-5">Status</dt><dd class="col-sm-7">{{ $val($employee->status) }}</dd>
                    </dl></div>
                    <div class="col-md-6"><dl class="row mb-0">
                        <dt class="col-sm-5">Tanggal Masuk</dt><dd class="col-sm-7">{{ $tgl($employee->tanggal_masuk) }}</dd>
                        <dt class="col-sm-5">Tanggal Keluar</dt><dd class="col-sm-7">{{ $tgl($employee->tanggal_keluar) }}</dd>
                        <dt class="col-sm-5">Perpanjangan Terakhir</dt><dd class="col-sm-7">{{ $tgl($employee->tanggal_perpanjangan_terakhir) }}</dd>
                        <dt class="col-sm-5">No. PKS Masuk</dt><dd class="col-sm-7">{{ $val($employee->no_pks_masuk) }}</dd>
                        <dt class="col-sm-5">No. PKS Perpanjangan</dt><dd class="col-sm-7">{{ $val($employee->no_pks_perpanjangan) }}</dd>
                    </dl></div>
                    <div class="col-12"><dl class="row mb-0">
                        <dt class="col-sm-2">Keterangan Perpanjangan</dt><dd class="col-sm-10">{{ $val($employee->keterangan_perpanjangan) }}</dd>
                        <dt class="col-sm-2">Keterangan Lain-lain</dt><dd class="col-sm-10">{{ $val($employee->note1) }}</dd>
                        <dt class="col-sm-2">Nama Perekrut / Referensi</dt><dd class="col-sm-10">{{ $val($employee->nama_perekrut) }}</dd>
                    </dl></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-header {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 140, 74, 0.1) 100%);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #FF6B35;
    }
    dt { font-weight: 500; color: #555; }
    dd { margin-bottom: 0.35rem; }
</style>
@endsection
