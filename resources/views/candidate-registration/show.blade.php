@extends('layouts.app')

@section('title', 'Detail Data Kandidat')
@section('page-title', 'Detail Data Kandidat')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $candidateRegistration->full_name ?: $candidateRegistration->candidate_name }}</h1>
        <p class="text-muted mb-0">Data yang dikirim melalui link kandidat.</p>
    </div>
    <a href="{{ route('candidate-registration.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            @foreach([
                'Nama Lengkap' => $candidateRegistration->full_name,
                'NIK KTP' => $candidateRegistration->nik_ktp,
                'No. HP' => $candidateRegistration->phone,
                'Email' => $candidateRegistration->email,
                'Tempat, Tanggal Lahir' => trim(($candidateRegistration->birth_place ?: '-') . ', ' . ($candidateRegistration->birth_date?->format('d/m/Y') ?: '-')),
                'Nama Ibu Kandung' => $candidateRegistration->mother_name,
                'Lokasi Kerja' => $candidateRegistration->work_location,
                'Bank' => $candidateRegistration->bank_name,
                'No. Rekening' => $candidateRegistration->bank_account_number,
                'A.N. Rekening' => $candidateRegistration->bank_account_holder,
            ] as $label => $value)
                <div class="col-md-6"><small class="text-muted d-block">{{ $label }}</small><strong>{{ $value ?: '-' }}</strong></div>
            @endforeach
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white"><strong><i class="bi bi-paperclip"></i> Dokumen</strong></div>
    <div class="card-body">
        @if($candidateRegistration->status === 'submitted')
            <div class="row g-2">
                @foreach(['ktp' => 'KTP', 'kk' => 'KK', 'ijazah' => 'Ijazah', 'cv-lamaran' => 'CV & Surat Lamaran'] as $key => $label)
                    @php
                        $column = ['ktp' => 'ktp_path', 'kk' => 'kk_path', 'ijazah' => 'diploma_path', 'cv-lamaran' => 'cv_path'][$key];
                        $path = $candidateRegistration->{$column};
                        $sizeKb = $path && Storage::disk('local')->exists($path)
                            ? max(1, (int) ceil(Storage::disk('local')->size($path) / 1024))
                            : null;
                    @endphp
                    <div class="col-md-4">
                        <a class="btn btn-outline-primary w-100" href="{{ route('candidate-registration.download', [$candidateRegistration, $key]) }}">
                            <i class="bi bi-download"></i> {{ $label }}{{ $sizeKb ? " ({$sizeKb} KB)" : '' }}
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">Dokumen belum dikirim oleh kandidat.</p>
        @endif
    </div>
</div>

<div class="d-flex gap-2 flex-wrap">
    @if($candidateRegistration->status === 'submitted' && !$candidateRegistration->applied_at)
        <form method="POST" action="{{ route('candidate-registration.apply', $candidateRegistration) }}" onsubmit="return confirm('Terapkan data ini ke master karyawan?')">
            @csrf
            <button class="btn btn-success"><i class="bi bi-check2-circle"></i> Terapkan ke Master Karyawan</button>
        </form>
    @endif
    @if($candidateRegistration->applied_at)
        <span class="badge bg-success d-flex align-items-center px-3">Sudah diterapkan {{ $candidateRegistration->applied_at->format('d/m/Y H:i') }}</span>
    @endif
    <form method="POST" action="{{ route('candidate-registration.destroy', $candidateRegistration) }}" onsubmit="return confirm('Hapus data dan seluruh dokumen kandidat?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button>
    </form>
</div>
@endsection
