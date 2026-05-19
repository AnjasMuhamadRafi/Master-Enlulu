@extends('layouts.app')

@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Karyawan')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 text-dark"><i class="bi bi-person-check" style="color: #FF6B35; margin-right: 8px;"></i> Edit Data Karyawan: {{ $employee->nama_lengkap }}</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">Form Edit Karyawan</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('employee.update', $employee) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">NIK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nik') is-invalid @enderror" 
                               name="nik" 
                               placeholder="Masukkan 16 angka NIK (contoh: 1234567890123456)" 
                               value="{{ old('nik', $employee->nik) }}" 
                               maxlength="16"
                               pattern="[0-9]{16}"
                               title="NIK harus terdiri dari 16 angka"
                               required>
                        <small class="form-text text-muted">NIK harus terdiri dari 16 angka</small>
                        @error('nik')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_ktp') is-invalid @enderror" 
                               name="nama_ktp" 
                               placeholder="Nama" 
                               value="{{ old('nama_ktp', $employee->nama_ktp) }}"
                               required>
                        @error('nama_ktp')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kode Vendor</label>
                        <input type="text" class="form-control @error('kode_vendor') is-invalid @enderror" name="kode_vendor" placeholder="Kode vendor" value="{{ old('kode_vendor', $employee->kode_vendor) }}">
                        @error('kode_vendor')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Posisi/Jabatan</label>
                        <select class="form-select @error('posisi') is-invalid @enderror" name="posisi">
                            <option value="">-- Pilih Posisi --</option>
                            
                            <!-- ADMIN/PIC Roles -->
                            <optgroup label="ADMIN/PIC Roles">
                                @foreach (config('positions.admin_pic_roles', []) as $role)
                                    <option value="{{ $role }}" {{ old('posisi', $employee->posisi) == $role ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </optgroup>
                            
                            <!-- Operational Positions -->
                            <optgroup label="Posisi Operasional">
                                @foreach (config('positions.operational_positions', []) as $pos)
                                    <option value="{{ $pos }}" {{ old('posisi', $employee->posisi) == $pos ? 'selected' : '' }}>
                                        {{ $pos }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        @error('posisi')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi Kerja</label>
                        <input type="text" class="form-control @error('penempatan') is-invalid @enderror" name="penempatan" placeholder="Lokasi penempatan" value="{{ old('penempatan', $employee->penempatan) }}">
                        @error('penempatan')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type Lokasi</label>
                        <input type="text" class="form-control @error('type_lokasi') is-invalid @enderror" name="type_lokasi" placeholder="Type lokasi" value="{{ old('type_lokasi', $employee->type_lokasi) }}">
                        @error('type_lokasi')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Area Kerja</label>
                        <input type="text" class="form-control @error('area_kerja') is-invalid @enderror" name="area_kerja" placeholder="Area kerja" value="{{ old('area_kerja', $employee->area_kerja) }}">
                        @error('area_kerja')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No. Rekening</label>
                        <input type="text" class="form-control @error('no_rekening') is-invalid @enderror" name="no_rekening" placeholder="Nomor rekening" value="{{ old('no_rekening', $employee->no_rekening) }}">
                        @error('no_rekening')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Bank</label>
                        <input type="text" class="form-control @error('nama_bank') is-invalid @enderror" name="nama_bank" placeholder="Nama bank" value="{{ old('nama_bank', $employee->nama_bank) }}">
                        @error('nama_bank')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama di Rekening</label>
                        <input type="text" class="form-control @error('nama_di_rekening') is-invalid @enderror" name="nama_di_rekening" placeholder="Nama pemilik rekening" value="{{ old('nama_di_rekening', $employee->nama_di_rekening) }}">
                        @error('nama_di_rekening')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Kerja</label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status">
                            <option value="Aktif" {{ old('status', $employee->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Training" {{ old('status', $employee->status) == 'Training' ? 'selected' : '' }}>Training</option>
                            <option value="Resign" {{ old('status', $employee->status) == 'Resign' ? 'selected' : '' }}>Resign</option>
                        </select>
                        @error('status')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control @error('note1') is-invalid @enderror" name="note1" placeholder="Catatan tambahan" rows="3">{{ old('note1', $employee->note1) }}</textarea>
                        @error('note1')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Perbarui
                        </button>
                        <a href="{{ route('employee.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .page-header {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 140, 74, 0.1) 100%);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #FF6B35;
    }

    .form-label {
        font-weight: 500;
        color: #333;
    }
</style>
@endsection
