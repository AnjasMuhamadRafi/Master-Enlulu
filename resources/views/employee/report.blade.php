@extends('layouts.app')

@section('title', 'Laporan Karyawan')
@section('page-title', 'Laporan Karyawan Enlulu')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 text-dark"><i class="bi bi-bar-chart" style="color: #FF6B35; margin-right: 8px;"></i> Laporan Karyawan</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('employee.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card summary-card" onclick="filterStatus('')" style="cursor:pointer;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total</h6>
                        <h3 class="mb-0">{{ $totalKaryawan }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #FF6B35; opacity: 0.2;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card" onclick="filterStatus('Aktif')" style="cursor:pointer;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Aktif</h6>
                        <h3 class="mb-0" style="color: #198754;">{{ $totalAktif }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #198754; opacity: 0.2;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card" onclick="filterStatus('Training')" style="cursor:pointer;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Training</h6>
                        <h3 class="mb-0" style="color: #fd7e14;">{{ $totalTraining }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #fd7e14; opacity: 0.2;">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card" onclick="filterStatus('Resign')" style="cursor:pointer;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Resign</h6>
                        <h3 class="mb-0" style="color: #dc3545;">{{ $totalResign }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #dc3545; opacity: 0.2;">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card" onclick="filterStatus('Fraud')" style="cursor:pointer;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Fraud</h6>
                        <h3 class="mb-0" style="color: #6f0000;">{{ $totalFraud }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #6f0000; opacity: 0.2;">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card" onclick="filterStatus('Cancel')" style="cursor:pointer;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Cancel</h6>
                        <h3 class="mb-0" style="color: #6c757d;">{{ $totalCancel }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #6c757d; opacity: 0.2;">
                        <i class="bi bi-slash-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Charts & Breakdown --}}
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">Karyawan per Lokasi</h5>
            </div>
            <div class="card-body">
                @if($byLocation->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <thead>
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th>Lokasi</th>
                                <th style="text-align: right; width: 100px;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byLocation as $loc)
                            <tr>
                                <td>{{ $loc->penempatan ?? '(Belum diisi)' }}</td>
                                <td style="text-align: right;">
                                    <span class="badge bg-info">{{ $loc->count }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0">Tidak ada data</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">Karyawan per Posisi</h5>
            </div>
            <div class="card-body">
                @if($byPosition->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <thead>
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th>Posisi</th>
                                <th style="text-align: right; width: 100px;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byPosition as $pos)
                            <tr>
                                <td>{{ $pos->posisi ?? '(Belum diisi)' }}</td>
                                <td style="text-align: right;">
                                    <span class="badge bg-warning">{{ $pos->count }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0">Tidak ada data</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Detailed List --}}
<div class="card mb-4">
    <div class="card-header bg-light border-bottom">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">Daftar Karyawan
                    @php
                    $badgeMap = [
                        'Aktif'    => 'success',
                        'Training' => 'warning',
                        'Resign'   => 'danger',
                        'Fraud'    => 'dark',
                        'Cancel'   => 'secondary',
                    ];
                    @endphp
                    <span class="badge bg-{{ $badgeMap[$statusFilter] ?? 'primary' }}">
                        {{ $statusFilter ?: 'Semua' }}
                    </span>
                </h5>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Cari NIK atau Nama..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="text"
                    name="posisi"
                    class="form-control"
                    placeholder="Cari posisi..."
                    value="{{ request('posisi') }}"
                    list="posisi-list">
                <datalist id="posisi-list">
                    @foreach($posisi as $pos)
                    <option value="{{ $pos }}">{{ $pos }}</option>
                    @endforeach
                </datalist>
            </div>
            <div class="col-md-2">
                <input type="text"
                    name="penempatan"
                    class="form-control"
                    placeholder="Cari lokasi..."
                    value="{{ request('penempatan') }}"
                    list="penempatan-list">
                <datalist id="penempatan-list">
                    @foreach($penempatan as $p)
                    <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </datalist>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="" {{ $statusFilter == '' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Aktif"    {{ $statusFilter == 'Aktif'    ? 'selected' : '' }}>Aktif</option>
                    <option value="Training" {{ $statusFilter == 'Training' ? 'selected' : '' }}>Training</option>
                    <option value="Resign"   {{ $statusFilter == 'Resign'   ? 'selected' : '' }}>Resign</option>
                    <option value="Fraud"    {{ $statusFilter == 'Fraud'    ? 'selected' : '' }}>Fraud</option>
                    <option value="Cancel"   {{ $statusFilter == 'Cancel'   ? 'selected' : '' }}>Cancel</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>

        @if($employees->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th>NIK KTP</th>
                        <th>NAMA</th>
                        <th>JABATAN</th>
                        <th>LOKASI</th>
                        <th>BANK</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $key => $emp)
                    <tr>
                        <td><strong>{{ ($employees->currentPage() - 1) * $employees->perPage() + $key + 1 }}</strong></td>
                        <td><strong>{{ $emp->nik_ktp ?? '-' }}</strong></td>
                        <td>{{ $emp->nama_lengkap }}</td>
                        <td>{{ $emp->posisi ?? '-' }}</td>
                        <td>{{ $emp->penempatan ?? '-' }}</td>
                        <td>{{ $emp->nama_bank ?? '-' }}</td>
                        <td>
                            @php
                            $statusClass = match($emp->status ?? 'Aktif') {
                                'Aktif'    => 'bg-success',
                                'Training' => 'bg-warning text-dark',
                                'Resign'   => 'bg-danger',
                                'Fraud'    => 'bg-dark',
                                'Cancel'   => 'bg-secondary',
                                default    => 'bg-secondary',
                            };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $emp->status ?? 'Aktif' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $employees->links() }}
        </div>
        @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Tidak ada data karyawan{{ $statusFilter ? ' ' . strtolower($statusFilter) : '' }}.
        </div>
        @endif
    </div>
</div>

<style>
    .page-header {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 140, 74, 0.1) 100%);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #FF6B35;
        margin-bottom: 20px;
    }

    .card {
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .summary-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
        transition: all 0.2s ease;
    }
</style>

<script>
function filterStatus(status) {
    const url = new URL(window.location.href);
    if (status === '') {
        url.searchParams.delete('status');
    } else {
        url.searchParams.set('status', status);
    }
    window.location.href = url.toString();
}
</script>
@endsection
