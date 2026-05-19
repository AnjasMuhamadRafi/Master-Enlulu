@extends('layouts.app')

@section('title', 'Buat Kontrak')
@section('page-title', 'Buat Kontrak Baru')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Form Buat Kontrak</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('contract.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Karyawan *</label>
                        <select class="form-control @error('employee_id') is-invalid @enderror" name="employee_id" required>
                            <option value="">Pilih Karyawan</option>
                        </select>
                        @error('employee_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai *</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" required>
                            @error('start_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Berakhir *</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" required>
                            @error('end_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jenis Kontrak *</label>
                        <select class="form-control @error('contract_type') is-invalid @enderror" name="contract_type" required>
                            <option value="">Pilih Jenis Kontrak</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Contract">Contract</option>
                            <option value="Internship">Internship</option>
                        </select>
                        @error('contract_type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="4" placeholder="Masukkan deskripsi kontrak"></textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <a href="{{ route('contract.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
