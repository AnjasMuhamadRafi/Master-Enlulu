@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('page-title', 'Daftar Karyawan Enlulu')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 text-dark"><i class="bi bi-people" style="color: #FF6B35; margin-right: 8px;"></i> Data Karyawan</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('employee.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Tambah Karyawan
            </a>
            <a href="{{ route('employee.download-template') }}" class="btn btn-secondary">
                <i class="bi bi-download"></i> Template
            </a>
            <a href="{{ route('employee.import') }}" class="btn btn-info">
                <i class="bi bi-upload"></i> Import CSV
            </a>
            <a href="{{ route('employee.export', ['search' => request('search'), 'posisi' => request('posisi'), 'penempatan' => request('penempatan'), 'status' => request('status')]) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export
            </a>
            <a href="{{ route('employee.report') }}" class="btn btn-warning">
                <i class="bi bi-bar-chart"></i> Laporan
            </a>
        </div>
    </div>
</div>

{{-- Import Success/Error Messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

{{-- Data Diperbarui dari Import --}}
@if(session('import_updated') && count(session('import_updated')) > 0)
<div class="alert alert-info mb-2">
    <h6 class="mb-2"><i class="bi bi-arrow-repeat"></i> <strong>Data Diperbarui dari Import ({{ count(session('import_updated')) }})</strong></h6>
    <div class="table-responsive">
        <table class="table table-sm table-borderless mb-0">
            <thead>
                <tr style="border-bottom: 2px solid #dee2e6;">
                    <th style="width: 140px;">NIK</th>
                    <th>Nama</th>
                    <th>Perubahan</th>
                </tr>
            </thead>
            <tbody>
                @foreach(session('import_updated') as $item)
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td>
                        <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 3px;">{{ $item['nik'] }}</code>
                    </td>
                    <td><strong>{{ $item['nama'] }}</strong></td>
                    <td>
                        <ul class="mb-0" style="list-style: none; padding: 0; font-size: 0.85rem;">
                            @foreach($item['changes'] as $field => $change)
                            <li style="margin: 3px 0;">
                                <strong>{{ str_replace('_', ' ', ucfirst($field)) }}:</strong>
                                <code style="color: #dc3545;">{{ $change['old'] }}</code> 
                                <i class="bi bi-arrow-right"></i> 
                                <code style="color: #198754;">{{ $change['new'] }}</code>
                            </li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Errors dari Import --}}
@if(session('errors') && count(session('errors')) > 0)
<div class="alert alert-danger mb-2">
    <strong><i class="bi bi-exclamation-triangle"></i> Error Import ({{ count(session('errors')) }}):</strong>
    <ul class="mb-0 mt-2">
        @foreach(session('errors') as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Data Ditambah Manual --}}
@if(session('manual_created'))
<div class="alert alert-success mb-2">
    <strong><i class="bi bi-person-plus-fill"></i> Data Karyawan Ditambahkan:</strong>
    <div class="mt-2">
        <code>{{ session('manual_created')['nik'] }}</code> - {{ session('manual_created')['nama'] }}
    </div>
</div>
@endif

{{-- Data Diperbarui Manual --}}
@if(session('manual_updated'))
<div class="alert alert-info mb-2">
    <strong><i class="bi bi-arrow-repeat"></i> Data Karyawan Diperbarui:</strong>
    <div class="mt-2">
        <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 3px;">{{ session('manual_updated')['nik'] }}</code> 
        <strong>{{ session('manual_updated')['nama'] }}</strong>
        @if(!empty(session('manual_updated')['changes']))
        <div style="margin-top: 8px; font-size: 0.85rem;">
            <ul style="list-style: none; padding: 0; margin: 0;">
                @foreach(session('manual_updated')['changes'] as $field => $change)
                <li style="margin: 3px 0;">
                    <strong>{{ str_replace('_', ' ', ucfirst($field)) }}:</strong>
                    <code style="color: #dc3545;">{{ $change['old'] ?? '-' }}</code> 
                    <i class="bi bi-arrow-right"></i> 
                    <code style="color: #198754;">{{ $change['new'] }}</code>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endif
@endif

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
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
                    placeholder="Cari penempatan..."
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
                    <option value="">Semua Status</option>
                    <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Training" {{ request('status') == 'Training' ? 'selected' : '' }}>Training</option>
                    <option value="Resign" {{ request('status') == 'Resign' ? 'selected' : '' }}>Resign</option>
                    <option value="Cancel" {{ request('status') == 'Cancel' ? 'selected' : '' }}>Cancel</option>
                    <option value="Fraud" {{ request('status') == 'Fraud' ? 'selected' : '' }}>Fraud</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
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
                    <label class="mb-0 text-muted" style="white-space: nowrap;">Data per halaman:</label>
                    <select class="form-select form-select-sm" style="width: 80px;" id="perPageSelect">
                        <option value="10" {{ request('per_page') == '10' || (empty(request('per_page')) && false) ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                        <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500</option>
                        <option value="1000" {{ request('per_page') == '1000' ? 'selected' : '' }}>1000</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="perPageForm" method="GET" style="display: none;">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="posisi" value="{{ request('posisi') }}">
    <input type="hidden" name="penempatan" value="{{ request('penempatan') }}">
    <input type="hidden" name="status" value="{{ request('status') }}">
    <input type="hidden" name="per_page" value="15">
</form>

<script>
    document.getElementById('perPageSelect').addEventListener('change', function(e) {
        const perPageInput = document.querySelector('input[name="per_page"]');
        perPageInput.value = this.value;
        document.getElementById('perPageForm').submit();
    });
</script>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">NO</th>
                    <th>NIK KTP</th>
                    <th>NAMA KTP</th>
                    <th>KLIEN</th>
                    <th>JABATAN</th>
                    <th>TYPE LOKASI</th>
                    <th>LOKASI KERJA</th>
                    <th>AREA KERJA</th>
                    <th>NO HP</th>
                    <th>TGL MASUK</th>
                    <th>STATUS KERJA</th>
                    <th>BANK</th>
                    <th>NOREK</th>
                    <th>NAMA REKENING</th>
                    <th>NOTE1</th>
                    <th style="width: 100px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $key => $emp)
                <tr>
                    <td><strong>{{ ($employees->currentPage() - 1) * $employees->perPage() + $key + 1 }}</strong></td>
                    <td><strong>{{ $emp->nik_ktp ?? '-' }}</strong></td>
                    <td>{{ $emp->nama_ktp ?? '-' }}</td>
                    <td>{{ $emp->klien ?? '-' }}</td>
                    <td>{{ $emp->posisi ?? '-' }}</td>
                    <td>{{ $emp->type_lokasi ?? '-' }}</td>
                    <td>{{ $emp->penempatan ?? '-' }}</td>
                    <td>{{ $emp->area_kerja ?? '-' }}</td>
                    <td>{{ $emp->no_hp ?? '-' }}</td>
                    <td>{{ optional($emp->tanggal_masuk)->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        @php
                        $statusClass = match($emp->status ?? 'Aktif') {
                        'Aktif' => 'bg-success',
                        'Training' => 'bg-warning',
                        'Resign' => 'bg-danger',
                        default => 'bg-secondary'
                        };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $emp->status ?? 'Aktif' }}</span>
                    </td>
                    <td>{{ $emp->nama_bank ?? '-' }}</td>
                    <td>{{ $emp->no_rekening ?? '-' }}</td>
                    <td>{{ $emp->nama_di_rekening ?? '-' }}</td>
                    <td>
                        @if($emp->note1)
                            <span title="{{ $emp->note1 }}" style="cursor: help;">
                                {{ Str::limit($emp->note1, 20, '...') }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('employee.show', $emp) }}" class="btn btn-sm btn-info" title="Detail">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('employee.edit', $emp) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('employee.destroy', $emp) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger"
                                onclick="openDeleteModal(this.closest('form'), '{{ $emp->nik }}', '{{ $emp->nama_lengkap }}', '{{ $emp->posisi ?? '-' }}', '{{ $emp->penempatan ?? '-' }}')">
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

{{ $employees->links() }}
@else
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Belum ada data karyawan.
    <a href="{{ route('employee.create') }}">Tambah karyawan baru</a>
</div>
@endif

<style>
    .page-header {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 140, 74, 0.1) 100%);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #FF6B35;
        margin-bottom: 20px;
    }

</style>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
  <div id="deleteBackdrop" onclick="closeDeleteModal()"
    style="position:absolute; inset:0; background:rgba(15,15,25,0.6); backdrop-filter:blur(4px);"></div>

  <div style="position:relative; background:#fff; border-radius:16px; padding:2rem; width:100%; max-width:420px; margin:1rem;
              box-shadow:0 25px 60px rgba(0,0,0,0.2); animation:popIn .25s cubic-bezier(.34,1.56,.64,1);">
    
    <!-- Icon -->
    <div style="width:60px; height:60px; background:#fff1f1; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem;">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#e53e3e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
        <path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
      </svg>
    </div>

    <!-- Title -->
    <h5 style="text-align:center; font-size:1.1rem; font-weight:700; color:#1a202c; margin:0 0 .4rem;">Hapus Data Karyawan?</h5>
    <p style="text-align:center; font-size:.8rem; color:#a0aec0; margin:0 0 1.25rem;">
      Data yang dihapus tidak dapat dikembalikan.
    </p>

    <!-- Employee Detail Card -->
    <div style="background:#f7fafc; border:1.5px solid #e2e8f0; border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.5rem;">
      <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:.75rem; padding-bottom:.75rem; border-bottom:1px solid #e2e8f0;">
        <!-- Avatar -->
        <div style="width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg,#FF6B35,#ff8c4a);
                    display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <div>
          <div id="modal-nama" style="font-weight:700; font-size:.95rem; color:#1a202c;"></div>
          <div id="modal-nik" style="font-size:.78rem; color:#718096; margin-top:1px;"></div>
        </div>
      </div>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:.5rem;">
        <div>
          <div style="font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:#a0aec0; margin-bottom:2px;">Posisi</div>
          <div id="modal-posisi" style="font-size:.82rem; font-weight:600; color:#2d3748;"></div>
        </div>
        <div>
          <div style="font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:#a0aec0; margin-bottom:2px;">Penempatan</div>
          <div id="modal-penempatan" style="font-size:.82rem; font-weight:600; color:#2d3748;"></div>
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div style="display:flex; gap:.75rem;">
      <button onclick="closeDeleteModal()"
        style="flex:1; padding:.65rem 1rem; border:1.5px solid #e2e8f0; border-radius:10px; background:#fff;
               color:#4a5568; font-size:.875rem; font-weight:600; cursor:pointer; transition:all .15s;">
        Batal
      </button>
      <button id="confirmDeleteBtn"
        style="flex:1; padding:.65rem 1rem; border:none; border-radius:10px; background:linear-gradient(135deg,#e53e3e,#c53030);
               color:#fff; font-size:.875rem; font-weight:600; cursor:pointer; transition:all .15s; box-shadow:0 4px 12px rgba(229,62,62,.35);">
        Ya, Hapus
      </button>
    </div>
  </div>
</div>

<style>
@keyframes popIn {
  from { opacity:0; transform:scale(.85) translateY(10px); }
  to   { opacity:1; transform:scale(1) translateY(0); }
}
</style>

<script>
let _deleteForm = null;

function openDeleteModal(form, nik, nama, posisi, penempatan) {
  _deleteForm = form;
  document.getElementById('modal-nik').textContent = 'NIK: ' + nik;
  document.getElementById('modal-nama').textContent = nama;
  document.getElementById('modal-posisi').textContent = posisi;
  document.getElementById('modal-penempatan').textContent = penempatan;

  const modal = document.getElementById('deleteModal');
  modal.style.display = 'flex';
}

function closeDeleteModal() {
  document.getElementById('deleteModal').style.display = 'none';
  _deleteForm = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
  if (_deleteForm) _deleteForm.submit();
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });
</script>

<style>
    @keyframes popIn {
        from {
            opacity: 0;
            transform: scale(.85) translateY(10px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
</style>
@endsection