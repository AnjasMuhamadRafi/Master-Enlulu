@extends('layouts.app')

@section('title', 'Edit BPU TK')
@section('page-title', 'Edit BPU TK')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 text-dark mb-0">
                <i class="bi bi-pencil" style="color: #FF6B35; margin-right: 8px;"></i> Edit BPU TK
            </h1>
            <p class="text-muted small mb-0 mt-1">{{ $bpuTk->nomor_identitas }} — {{ $bpuTk->nama_lengkap }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('employee.bpu-tk') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

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

<div class="card">
    <div class="card-body">
        <form action="{{ route('employee.bpu-tk.update', $bpuTk) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">NOMOR_IDENTITAS (NIK KTP) <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_identitas" class="form-control @error('nomor_identitas') is-invalid @enderror"
                           maxlength="16" placeholder="16 digit NIK KTP"
                           value="{{ old('nomor_identitas', $bpuTk->nomor_identitas) }}" required>
                    @error('nomor_identitas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">NAMA_LENGKAP <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror"
                           placeholder="Nama sesuai KTP"
                           value="{{ old('nama_lengkap', $bpuTk->nama_lengkap) }}" required>
                    @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">TGL_LAHIR</label>
                    <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                           value="{{ old('tanggal_lahir', $bpuTk->tanggal_lahir?->format('Y-m-d')) }}">
                    @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">HANDPHONE</label>
                    <input type="text" name="handphone" class="form-control @error('handphone') is-invalid @enderror"
                           placeholder="08xxxxxxxxxx"
                           value="{{ old('handphone', $bpuTk->handphone) }}">
                    @error('handphone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">EMAIL</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           placeholder="email@contoh.com"
                           value="{{ old('email', $bpuTk->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">JENIS_PEKERJAAN_1</label>
                    <input type="text" name="jenis_pekerjaan_1" class="form-control @error('jenis_pekerjaan_1') is-invalid @enderror"
                           placeholder="cth: Kurir"
                           value="{{ old('jenis_pekerjaan_1', $bpuTk->jenis_pekerjaan_1) }}">
                    @error('jenis_pekerjaan_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">JENIS_PEKERJAAN_2 <small class="text-muted">(opsional)</small></label>
                    <input type="text" name="jenis_pekerjaan_2" class="form-control @error('jenis_pekerjaan_2') is-invalid @enderror"
                           placeholder="opsional"
                           value="{{ old('jenis_pekerjaan_2', $bpuTk->jenis_pekerjaan_2) }}">
                    @error('jenis_pekerjaan_2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">LOKASI_PEKERJAAN</label>
                    <input type="text" name="lokasi_pekerjaan" class="form-control @error('lokasi_pekerjaan') is-invalid @enderror"
                           placeholder="cth: Jakarta Selatan"
                           value="{{ old('lokasi_pekerjaan', $bpuTk->lokasi_pekerjaan) }}">
                    @error('lokasi_pekerjaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">UPAH</label>
                    <input type="number" name="upah" class="form-control @error('upah') is-invalid @enderror"
                           placeholder="cth: 2000000" min="0"
                           value="{{ old('upah', $bpuTk->upah) }}">
                    @error('upah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">KODE_PAKET</label>
                    <select name="kode_paket" class="form-select @error('kode_paket') is-invalid @enderror">
                        <option value="T" {{ old('kode_paket', $bpuTk->kode_paket) == 'T' ? 'selected' : '' }}>T</option>
                        <option value="TI" {{ old('kode_paket', $bpuTk->kode_paket) == 'TI' ? 'selected' : '' }}>TI</option>
                    </select>
                    @error('kode_paket')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">BULAN_IURAN</label>
                    <select name="bulan_iuran" class="form-select @error('bulan_iuran') is-invalid @enderror">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('bulan_iuran', $bpuTk->bulan_iuran) == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    @error('bulan_iuran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('employee.bpu-tk') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
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
@endsection
