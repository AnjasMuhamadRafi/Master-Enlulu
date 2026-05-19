@extends('layouts.app')

@section('title', 'Report Karyawan Aktif')
@section('page-title', 'Report Karyawan Aktif')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 text-dark"><i class="bi bi-bar-chart" style="color: #FF6B35; margin-right: 8px;"></i> Report Karyawan Aktif</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('employee.export', ['status' => 'Aktif']) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <input type="text" name="search" class="form-control" placeholder="Cari NIK atau Nama..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
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
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <input type="text"
                    name="penempatan"
                    class="form-control"
                    placeholder="Cari penempatan..."
                    value="{{ request('penempatan') }}"
                    list="penempatan-list">
                <datalist id="penempatan-list">
                    @foreach($penempatan as $p)
                    <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </datalist>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>
    </div>
</div>

@if($employees->count() > 0)
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col">
                <small class="text-muted">Menampilkan <strong>{{ $employees->count() }}</strong> dari <strong>{{ $employees->total() }}</strong> total data</small>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2 align-items-center">
                    <label class="form-label mb-0">Data per halaman:</label>
                    <select class="form-select form-select-sm d-inline w-auto" id="perPageSelect">
                        <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
            <script>
                document.getElementById('perPageSelect').addEventListener('change', function() {
                    const params = new URLSearchParams(window.location.search);
                    params.set('per_page', this.value);
                    window.location.href = window.location.pathname + '?' + params.toString();
                });
            </script>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 140px;">NIK</th>
                    <th style="min-width: 180px;">Nama Lengkap</th>
                    <th style="min-width: 130px;">Posisi</th>
                    <th style="min-width: 130px;">Penempatan</th>
                    <th style="min-width: 120px;">No. Rekening</th>
                    <th style="min-width: 100px;">Nama Bank</th>
                    <th style="min-width: 140px;">Nama di Rekening</th>
                    <th style="width: 80px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                    <tr>
                        <td>
                            <small><strong>{{ $employee->nik }}</strong></small>
                        </td>
                        <td>
                            <small>{{ $employee->nama_lengkap }}</small>
                        </td>
                        <td>
                            <small>{{ $employee->posisi ?? '-' }}</small>
                        </td>
                        <td>
                            <small>{{ $employee->penempatan ?? '-' }}</small>
                        </td>
                        <td>
                            <small>{{ $employee->no_rekening ?? '-' }}</small>
                        </td>
                        <td>
                            <small>{{ $employee->nama_bank ?? '-' }}</small>
                        </td>
                        <td>
                            <small>{{ $employee->nama_di_rekening ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $employee->status ?? 'Aktif' }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($employees->hasPages())
<div class="d-flex justify-content-center mt-4 mb-4">
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm mb-0 flex-wrap">
            {{-- Previous Page Link --}}
            @if ($employees->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">← Previous</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $employees->previousPageUrl() }}&search={{ request('search') }}&posisi={{ request('posisi') }}&penempatan={{ request('penempatan') }}">← Previous</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($employees->getUrlRange(1, $employees->lastPage()) as $page => $url)
                @if ($page == $employees->currentPage())
                    <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $url }}&search={{ request('search') }}&posisi={{ request('posisi') }}&penempatan={{ request('penempatan') }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($employees->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $employees->nextPageUrl() }}&search={{ request('search') }}&posisi={{ request('posisi') }}&penempatan={{ request('penempatan') }}">Next →</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">Next →</span>
                </li>
            @endif
        </ul>
    </nav>
</div>
@endif
@else
    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle"></i> Tidak ada data karyawan aktif yang ditemukan.
    </div>
@endif

@endsection
