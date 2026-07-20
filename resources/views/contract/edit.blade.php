@extends('layouts.app')

@section('title', 'Edit Kontrak')
@section('page-title', 'Edit Kontrak')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Edit Data Kontrak</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Karyawan</label>
                    <input class="form-control" value="{{ $contract->employee?->nama_lengkap }} ({{ $contract->employee_nik }})" readonly>
                </div>
                <form method="POST" action="{{ route('contract.update', $contract) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">No. PKS *</label>
                        <input type="text" class="form-control" name="contract_number" value="{{ old('contract_number', $contract->contract_number) }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Dibuat tanggal *</label>
                            <input type="date" class="form-control" name="contract_date" value="{{ old('contract_date', $contract->contract_date?->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal mulai *</label>
                            <input type="date" class="form-control" name="start_date" value="{{ old('start_date', $contract->start_date?->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal berakhir</label>
                            <input type="date" class="form-control" name="end_date" value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    <a href="{{ route('contract.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
