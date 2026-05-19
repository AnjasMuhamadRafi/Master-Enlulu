@extends('layouts.app')

@section('title', 'Data Master Enlulu')
@section('page-title', 'Data Master Enlulu')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2 style="margin-bottom: 30px; color: #1a1a1a; font-weight: 600;">
            Selamat Datang, <span style="color: #FF6B35;">{{ auth()->user()->name }}</span>!
        </h2>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card" style="border-left: 4px solid #FF6B35;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 12px;">TOTAL KARYAWAN</p>
                        <h3 style="color: #FF6B35; font-weight: bold; margin: 0;">{{ $employee_count ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-people-fill" style="font-size: 40px; color: #FF6B35; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card" style="border-left: 4px solid #4CAF50;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 12px;">KARYAWAN AKTIF</p>
                        <h3 style="color: #4CAF50; font-weight: bold; margin: 0;">{{ $active_employee ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-check-circle-fill" style="font-size: 40px; color: #4CAF50; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card" style="border-left: 4px solid #FF9800;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 12px;">KARYAWAN TRAINING</p>
                        <h3 style="color: #FF9800; font-weight: bold; margin: 0;">{{ $training_employee ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-hourglass-bottom" style="font-size: 40px; color: #FF9800; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card" style="border-left: 4px solid #F44336;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 12px;">KARYAWAN TIDAK AKTIF</p>
                        <h3 style="color: #F44336; font-weight: bold; margin: 0;">{{ $inactive_employee ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-x-circle-fill" style="font-size: 40px; color: #F44336; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card" style="border-left: 4px solid #2196F3;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 12px;">USER TERDAFTAR</p>
                        <h3 style="color: #2196F3; font-weight: bold; margin: 0;">{{ $user_count ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-person-circle" style="font-size: 40px; color: #2196F3; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card" style="border-left: 4px solid #FF9800;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 12px;">ROLE ANDA</p>
                        <h3 style="color: #FF9800; font-weight: bold; margin: 0;">{{ auth()->user()->role }}</h3>
                    </div>
                    <i class="bi bi-shield-check" style="font-size: 40px; color: #FF9800; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightning-charge" style="color: #FF6B35;"></i>
                    Akses Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('employee.create') }}" class="btn btn-primary w-100" style="border-radius: 8px; padding: 12px; font-weight: 600;">
                            <i class="bi bi-plus-circle"></i> Tambah Karyawan Baru
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('employee.import') }}" class="btn btn-primary w-100" style="border-radius: 8px; padding: 12px; font-weight: 600;">
                            <i class="bi bi-file-earmark-excel"></i> Import dari Excel
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('contract.create') }}" class="btn btn-primary w-100" style="border-radius: 8px; padding: 12px; font-weight: 600;">
                            <i class="bi bi-plus-circle"></i> Buat Kontrak Baru
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('employee.index') }}" class="btn btn-primary w-100" style="border-radius: 8px; padding: 12px; font-weight: 600;">
                            <i class="bi bi-list"></i> Lihat Daftar Karyawan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle" style="color: #FF6B35;"></i>
                    Informasi Sistem
                </h5>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 15px;">
                    <p style="color: #999; font-size: 12px; margin-bottom: 5px;">Versi Aplikasi</p>
                    <p style="font-weight: 600; margin: 0;">1.0.0</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <p style="color: #999; font-size: 12px; margin-bottom: 5px;">Tanggal Login Terakhir</p>
                    <p style="font-weight: 600; margin: 0;">{{ date('d F Y H:i') }}</p>
                </div>
                <div style="margin-bottom: 15px;">
                    <p style="color: #999; font-size: 12px; margin-bottom: 5px;">Status Koneksi Database</p>
                    <p style="font-weight: 600; margin: 0; color: #4CAF50;">
                        <i class="bi bi-check-circle-fill"></i> Terhubung
                    </p>
                </div>
                <hr>
                <p style="color: #999; font-size: 12px; margin: 0;">
                    Tim Enlulu - Database Management System 2026
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ADMIN/PIC Management Widget -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-shield-check" style="color: #FF6B35;"></i>
                    Manajemen ADMIN/PIC - Posisi yang Ditangani
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead style="background-color: #f5f5f5;">
                            <tr>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Posisi</th>
                                <th>Posisi yang Dikelola</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($adminPicEmployees ?? [] as $employee)
                                @if ($employee->isAdminPic())
                                    <tr>
                                        <td><strong>{{ $employee->nik }}</strong></td>
                                        <td>{{ $employee->nama_ktp }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $employee->posisi }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $managedPos = $employee->getManagedPositions();
                                            @endphp
                                            @if (count($managedPos) > 0)
                                                <div style="font-size: 13px;">
                                                    @foreach ($managedPos as $pos)
                                                        <span class="badge bg-light text-dark" style="margin: 2px;">{{ $pos }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match($employee->status ?? 'Aktif') {
                                                    'Aktif' => 'bg-success',
                                                    'Training' => 'bg-warning',
                                                    'Resign' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ $employee->status ?? 'Aktif' }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('employee.edit', $employee->nik) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-info-circle"></i> Belum ada ADMIN/PIC yang terdaftar
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history" style="color: #FF6B35;"></i>
                    Aktivitas Terbaru
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted text-center">Belum ada aktivitas. Mulai dengan menambahkan data karyawan atau membuat kontrak baru.</p>
            </div>
        </div>
    </div>


@endsection
