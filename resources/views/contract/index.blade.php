@extends('layouts.app')

@section('title', 'Daftar Kontrak')
@section('page-title', 'Daftar Kontrak')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Manajemen Kontrak</h4>
        <p class="text-muted mb-0">Generate perjanjian dari data master karyawan.</p>
    </div>
    <a href="{{ route('contract.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Buat Kontrak Baru</a>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>No. PKS</th>
                    <th>Karyawan</th>
                    <th>Klien</th>
                    <th>Dibuat</th>
                    <th>Masa Kontrak</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contracts as $contract)
                    <tr>
                        <td><strong>{{ $contract->contract_number }}</strong></td>
                        <td>
                            {{ $contract->employee?->nama_lengkap ?? $contract->employee_nik }}
                            <small class="d-block text-muted">{{ $contract->employee_nik }}</small>
                        </td>
                        <td>{{ $contract->employee?->nama_customer ?: '-' }}</td>
                        <td>{{ optional($contract->contract_date)->format('d/m/Y') }}</td>
                        <td>
                            {{ optional($contract->start_date)->format('d/m/Y') }}
                            -
                            {{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Tidak ditentukan' }}
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('contract.download', $contract) }}" class="btn btn-sm btn-success" title="Unduh DOCX">
                                <i class="bi bi-download"></i>
                            </a>
                            <a href="{{ route('contract.edit', $contract) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('contract.destroy', $contract) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kontrak ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data kontrak.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $contracts->links() }}
@endsection
