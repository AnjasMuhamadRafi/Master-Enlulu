@extends('layouts.app')

@section('title', 'Kelengkapan Data Kandidat')
@section('page-title', 'Kelengkapan Data Kandidat')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-ui-checks-grid text-warning"></i> Kelengkapan Data</h1>
        <p class="text-muted mb-0">Bagikan form umum dan tinjau data serta dokumen kandidat.</p>
    </div>
    <a href="{{ route('candidate-registration.export', request()->only(['search', 'status'])) }}" class="btn btn-success">
        <i class="bi bi-file-earmark-excel"></i> Export Excel
    </a>
</div>

<div class="card mb-4">
    <div class="card-header bg-white"><strong><i class="bi bi-link-45deg"></i> Link Form Kandidat</strong></div>
    <div class="card-body">
        <p class="text-muted">Bagikan link yang sama ini kepada seluruh kandidat.</p>
        <div class="input-group">
            <input id="registration-url" class="form-control" value="{{ route('public.candidate-registration.show') }}" readonly>
            <a href="{{ route('public.candidate-registration.show') }}" target="_blank" class="btn btn-outline-secondary" title="Buka form">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
            <button class="btn btn-warning" type="button" onclick="copyRegistrationUrl()"><i class="bi bi-copy"></i> Salin Link</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5"><input name="search" class="form-control" placeholder="Cari nama atau NIK KTP..." value="{{ request('search') }}"></div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Menunggu kandidat</option>
                    <option value="submitted" @selected(request('status') === 'submitted')>Sudah dikirim</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Cari</button></div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Kandidat</th><th>Status</th><th>Waktu Pengisian</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($registrations as $registration)
                    <tr>
                        <td><strong>{{ $registration->full_name ?: $registration->candidate_name }}</strong><small class="d-block text-muted">{{ $registration->nik_ktp ?: 'NIK belum diisi' }}</small></td>
                        <td>
                            @if($registration->status === 'submitted')
                                <span class="badge bg-success">Sudah dikirim</span>
                            @elseif($registration->isExpired())
                                <span class="badge bg-danger">Kedaluwarsa</span>
                            @else
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            @endif
                        </td>
                        <td>{{ $registration->submitted_at?->format('d/m/Y H:i') ?: '-' }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('candidate-registration.show', $registration) }}" title="Lihat detail"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kandidat yang mengisi form.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $registrations->links() }}
    </div>
</div>
@endsection

@section('js')
<script>
function copyText(value) {
    navigator.clipboard.writeText(value).then(() => alert('Link berhasil disalin.'));
}
function copyRegistrationUrl() {
    copyText(document.getElementById('registration-url').value);
}
</script>
@endsection
