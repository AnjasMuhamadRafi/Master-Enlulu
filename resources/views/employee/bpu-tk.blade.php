@extends('layouts.app')

@section('title', 'BPU TK')
@section('page-title', 'BPU TK')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center g-3">
        <div class="col">
            <h1 class="h3 text-dark mb-0">
                <i class="bi bi-shield-plus" style="color: #FF6B35; margin-right: 8px;"></i> BPU TK
            </h1>
            <p class="text-muted small mb-0 mt-1">Data Bukan Penerima Upah — Tenaga Kerja (BPJS Ketenagakerjaan)</p>
        </div>
        <div class="col-auto d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle"></i> Tambah Manual
            </button>
            <a href="{{ route('employee.bpu-tk.template') }}" class="btn btn-secondary">
                <i class="bi bi-file-earmark-spreadsheet"></i> Template
            </a>
            <a href="{{ route('employee.bpu-tk.export', request()->query()) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export
            </a>
        </div>
    </div>
</div>

{{-- Alert sukses --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Alert error --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Error import --}}
@if(session('bpu_tk_errors') && count(session('bpu_tk_errors')) > 0)
<div class="alert alert-danger alert-dismissible fade show">
    <strong><i class="bi bi-x-circle"></i> Error Import ({{ count(session('bpu_tk_errors')) }} baris):</strong>
    <ul class="mb-0 mt-2">
        @foreach(session('bpu_tk_errors') as $e)
            <li>{{ $e }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Warning import: belum ada di master --}}
@if(session('bpu_tk_warnings') && count(session('bpu_tk_warnings')) > 0)
<div class="alert alert-warning alert-dismissible fade show">
    <strong><i class="bi bi-exclamation-triangle"></i> Peringatan: {{ count(session('bpu_tk_warnings')) }} data belum ada di Master Karyawan</strong>
    <p class="mb-1 mt-1 small">Data sudah tersimpan di BPU TK. Mohon lengkapi di menu <a href="{{ route('employee.import') }}">Import Karyawan</a> agar terhubung ke master.</p>
    <details>
        <summary class="small" style="cursor:pointer;">Lihat detail</summary>
        <ul class="mb-0 mt-1 small">
            @foreach(session('bpu_tk_warnings') as $w)
                <li>{{ $w }}</li>
            @endforeach
        </ul>
    </details>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Validation errors (manual input) --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <strong><i class="bi bi-exclamation-triangle"></i> Data tidak valid:</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
            <div class="card-body py-3">
                <div class="text-muted small">Total Data</div>
                <div class="h4 mb-0">{{ number_format($summary['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
            <div class="card-body py-3">
                <div class="text-muted small">Terhubung Master</div>
                <div class="h4 mb-0 text-success">{{ number_format($summary['linked']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
            <div class="card-body py-3">
                <div class="text-muted small">Belum di Master</div>
                <div class="h4 mb-0 text-warning">{{ number_format($summary['not_linked']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 text-center">
            <div class="card-body py-3">
                <div class="text-muted small">Data Lengkap</div>
                <div class="h4 mb-0 text-primary">{{ number_format($summary['lengkap']) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Import & Filter --}}
<div class="row g-3 mb-4">
    {{-- Import Form --}}
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title mb-3"><i class="bi bi-upload"></i> Import Template BPU TK</h6>
                <form action="{{ route('employee.bpu-tk.import') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-end flex-wrap">
                    @csrf
                    <div class="flex-grow-1">
                        <input type="file" name="file" class="form-control form-control-sm @error('file') is-invalid @enderror"
                               accept=".xlsx,.xls,.csv,.txt" required>
                        @error('file')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload"></i> Import
                    </button>
                </form>
                <p class="text-muted small mt-2 mb-0">
                    <i class="bi bi-info-circle"></i>
                    Jika NIK belum ada di master karyawan, data tetap tersimpan dengan status <span class="badge bg-warning text-dark">Belum di Master</span>
                </p>
            </div>
        </div>
    </div>
    {{-- Filter --}}
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title mb-3"><i class="bi bi-funnel"></i> Filter</h6>
                <form method="GET" class="row g-2">
                    <div class="col-12 col-sm-5">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Cari NIK, nama..." value="{{ request('search') }}">
                    </div>
                    <div class="col-12 col-sm-5">
                        <select name="bpu_status" class="form-select form-select-sm">
                            <option value="">Semua Data</option>
                            <option value="lengkap" {{ request('bpu_status') == 'lengkap' ? 'selected' : '' }}>Data Lengkap</option>
                            <option value="belum_lengkap" {{ request('bpu_status') == 'belum_lengkap' ? 'selected' : '' }}>Belum Lengkap</option>
                            <option value="tgl_lahir_kosong" {{ request('bpu_status') == 'tgl_lahir_kosong' ? 'selected' : '' }}>Tgl Lahir Kosong</option>
                            <option value="kontak_kosong" {{ request('bpu_status') == 'kontak_kosong' ? 'selected' : '' }}>HP/Email Kosong</option>
                            <option value="jenis_pekerjaan_kosong" {{ request('bpu_status') == 'jenis_pekerjaan_kosong' ? 'selected' : '' }}>Jenis Pekerjaan Kosong</option>
                            <option value="lokasi_pekerjaan_kosong" {{ request('bpu_status') == 'lokasi_pekerjaan_kosong' ? 'selected' : '' }}>Lokasi Pekerjaan Kosong</option>
                            <option value="upah_kosong" {{ request('bpu_status') == 'upah_kosong' ? 'selected' : '' }}>Upah Kosong</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-search"></i>
                        </button>
                        @if(request('search') || request('bpu_status'))
                        <a href="{{ route('employee.bpu-tk') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x"></i>
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Data per halaman --}}
@if($records->count() > 0)
<div class="card mb-2">
    <div class="card-body py-2">
        <div class="row align-items-center g-2">
            <div class="col">
                <small class="text-muted">
                    Menampilkan <strong>{{ $records->count() }}</strong> dari <strong>{{ $records->total() }}</strong> data
                </small>
            </div>
            <div class="col-auto">
                <form method="GET" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="bpu_status" value="{{ request('bpu_status') }}">
                    <label class="mb-0 text-muted small" style="white-space:nowrap;">Per halaman:</label>
                    <select name="per_page" class="form-select form-select-sm" style="width:80px;" onchange="this.form.submit()">
                        @foreach([10,25,50,100,500,1000] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 15) == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Data --}}
<div class="card mb-3">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:0.85rem;">
            <thead class="table-light">
                <tr>
                    <th style="width:45px;">NO</th>
                    <th style="min-width:80px;">Status</th>
                    <th style="min-width:160px;">NOMOR_IDENTITAS</th>
                    <th style="min-width:180px;">NAMA_LENGKAP</th>
                    <th style="min-width:110px;">TGL_LAHIR</th>
                    <th style="min-width:120px;">HANDPHONE</th>
                    <th style="min-width:170px;">EMAIL</th>
                    <th style="min-width:150px;">JENIS_PEKERJAAN_1</th>
                    <th style="min-width:150px;">JENIS_PEKERJAAN_2</th>
                    <th style="min-width:150px;">LOKASI_PEKERJAAN</th>
                    <th style="min-width:100px;">UPAH</th>
                    <th style="min-width:100px;">KODE_PAKET</th>
                    <th style="min-width:100px;">BULAN_IURAN</th>
                    <th style="width:90px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $key => $rec)
                @php
                    $linked = isset($masterNiks[$rec->nomor_identitas]);
                @endphp
                <tr>
                    <td><strong>{{ ($records->currentPage() - 1) * $records->perPage() + $key + 1 }}</strong></td>
                    <td>
                        @if($linked)
                            <span class="badge bg-success" title="NIK ditemukan di master karyawan">
                                <i class="bi bi-check-circle"></i> Master
                            </span>
                        @else
                            <span class="badge bg-warning text-dark" title="NIK belum ada di master karyawan">
                                <i class="bi bi-exclamation-triangle"></i> Belum Master
                            </span>
                        @endif
                    </td>
                    <td><strong>{{ $rec->nomor_identitas }}</strong></td>
                    <td>{{ $rec->nama_lengkap ?: '-' }}</td>
                    <td>{{ $rec->tanggal_lahir ? $rec->tanggal_lahir->format('d-m-Y') : '-' }}</td>
                    <td>{{ $rec->handphone ?: '-' }}</td>
                    <td>{{ $rec->email ?: '-' }}</td>
                    <td>{{ $rec->jenis_pekerjaan_1 ?: '-' }}</td>
                    <td>{{ $rec->jenis_pekerjaan_2 ?: '-' }}</td>
                    <td>{{ $rec->lokasi_pekerjaan ?: '-' }}</td>
                    <td>{{ $rec->upah ? number_format($rec->upah, 0, ',', '.') : '-' }}</td>
                    <td>{{ $rec->kode_paket ?: 'T' }}</td>
                    <td>{{ $rec->bulan_iuran ?: 1 }}</td>
                    <td>
                        <a href="{{ route('employee.bpu-tk.edit', $rec) }}" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('employee.bpu-tk.destroy', $rec) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus data {{ addslashes($rec->nama_lengkap) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{ $records->links() }}
@else
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Tidak ada data BPU TK.
    <a href="#" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah manual</a> atau import dari template.
</div>
@endif

{{-- ===================== MODAL TAMBAH ===================== --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">
                    <i class="bi bi-plus-circle"></i> Tambah Data BPU TK
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employee.bpu-tk.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NOMOR_IDENTITAS (NIK KTP) <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_identitas" class="form-control @error('nomor_identitas') is-invalid @enderror"
                                   maxlength="16" placeholder="16 digit NIK KTP"
                                   value="{{ old('nomor_identitas') }}" required>
                            @error('nomor_identitas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NAMA_LENGKAP <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror"
                                   placeholder="Nama sesuai KTP"
                                   value="{{ old('nama_lengkap') }}" required>
                            @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">TGL_LAHIR</label>
                            <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                   value="{{ old('tanggal_lahir') }}">
                            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">HANDPHONE</label>
                            <input type="text" name="handphone" class="form-control @error('handphone') is-invalid @enderror"
                                   placeholder="08xxxxxxxxxx"
                                   value="{{ old('handphone') }}">
                            @error('handphone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">EMAIL</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   placeholder="email@contoh.com"
                                   value="{{ old('email') }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">JENIS_PEKERJAAN_1</label>
                            <input type="text" name="jenis_pekerjaan_1" class="form-control @error('jenis_pekerjaan_1') is-invalid @enderror"
                                   placeholder="cth: KURIR MOTOR"
                                   list="bpu-position-options"
                                   value="{{ old('jenis_pekerjaan_1') }}">
                            @error('jenis_pekerjaan_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">JENIS_PEKERJAAN_2 <small class="text-muted">(opsional)</small></label>
                            <input type="text" name="jenis_pekerjaan_2" class="form-control @error('jenis_pekerjaan_2') is-invalid @enderror"
                                   placeholder="opsional"
                                   list="bpu-position-options"
                                   value="{{ old('jenis_pekerjaan_2') }}">
                            @error('jenis_pekerjaan_2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">LOKASI_PEKERJAAN</label>
                            <input type="text" name="lokasi_pekerjaan" class="form-control @error('lokasi_pekerjaan') is-invalid @enderror"
                                   placeholder="cth: Jakarta Selatan"
                                   list="bpu-location-options"
                                   value="{{ old('lokasi_pekerjaan') }}">
                            @error('lokasi_pekerjaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <datalist id="bpu-position-options">
                            @foreach($positionOptions ?? [] as $position)
                                <option value="{{ $position }}"></option>
                            @endforeach
                        </datalist>
                        <datalist id="bpu-location-options">
                            @foreach($locationOptions ?? [] as $location)
                                <option value="{{ $location }}"></option>
                            @endforeach
                        </datalist>
                        <div class="col-md-6">
                            <label class="form-label">UPAH</label>
                            <input type="number" name="upah" class="form-control @error('upah') is-invalid @enderror"
                                   placeholder="cth: 2000000" min="0"
                                   value="{{ old('upah') }}">
                            @error('upah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">KODE_PAKET</label>
                            <select name="kode_paket" class="form-select @error('kode_paket') is-invalid @enderror">
                                <option value="T" {{ old('kode_paket', 'T') == 'T' ? 'selected' : '' }}>T</option>
                                <option value="TI" {{ old('kode_paket') == 'TI' ? 'selected' : '' }}>TI</option>
                            </select>
                            @error('kode_paket')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">BULAN_IURAN</label>
                            <select name="bulan_iuran" class="form-select @error('bulan_iuran') is-invalid @enderror">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('bulan_iuran', 1) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('bulan_iuran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.page-header {
    background: linear-gradient(135deg, rgba(255,107,53,0.1) 0%, rgba(255,140,74,0.1) 100%);
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #FF6B35;
}
</style>

{{-- Buka modal otomatis jika ada validation error (dari form tambah) --}}
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = new bootstrap.Modal(document.getElementById('modalTambah'));
        modal.show();
    });
</script>
@endif

@endsection
