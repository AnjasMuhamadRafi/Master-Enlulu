@extends('layouts.app')

@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 text-dark"><i class="bi bi-person-plus" style="color: #FF6B35; margin-right: 8px;"></i> Tambah Karyawan Baru</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">Form Input Karyawan</h5>
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

                <!-- Tab untuk Input Manual vs Paste Otomatis -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab">
                            <i class="bi bi-pencil"></i> Input Manual
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="paste-tab" data-bs-toggle="tab" data-bs-target="#paste" type="button" role="tab">
                            <i class="bi bi-clipboard"></i> Paste Otomatis
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Manual Input Tab -->
                    <div class="tab-pane fade show active" id="manual" role="tabpanel">
                        <form method="POST" action="{{ route('employee.store') }}" id="manualForm" enctype="multipart/form-data">
                    @csrf

                    {{-- ============ A. BIODATA ============ --}}
                    <h6 class="section-title">A. Biodata</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIK KTP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nik') is-invalid @enderror"
                                   name="nik" placeholder="16 angka NIK KTP" value="{{ old('nik') }}"
                                   maxlength="16" pattern="[0-9]{16}" title="NIK harus terdiri dari 16 angka" required>
                            <small class="form-text text-muted">NIK harus terdiri dari 16 angka</small>
                            @error('nik')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Sesuai KTP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_ktp') is-invalid @enderror" name="nama_ktp" placeholder="Nama sesuai KTP" value="{{ old('nama_ktp') }}" required>
                            @error('nama_ktp')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK ENLULU</label>
                            <input type="text" class="form-control @error('nik_enlulu') is-invalid @enderror" name="nik_enlulu" placeholder="NIK sementara dari PT Enlulu" value="{{ old('nik_enlulu') }}">
                            <small class="form-text text-muted">NIK sementara yang diterbitkan PT Enlulu</small>
                            @error('nik_enlulu')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK OS</label>
                            <input type="text" class="form-control @error('nik_os') is-invalid @enderror" name="nik_os" placeholder="NIK dari client / outsourcing" value="{{ old('nik_os') }}">
                            <small class="form-text text-muted">NIK yang diberikan oleh client</small>
                            @error('nik_os')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required>
                            @error('tempat_lahir')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                            @error('tanggal_lahir')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select @error('jenis_kelamin') is-invalid @enderror" name="jenis_kelamin" required>
                                <option value="">-- Pilih --</option>
                                @foreach (['Pria', 'Wanita'] as $opt)
                                    <option value="{{ $opt }}" {{ old('jenis_kelamin') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('jenis_kelamin')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agama <span class="text-danger">*</span></label>
                            <select class="form-select @error('agama') is-invalid @enderror" name="agama" required>
                                <option value="">-- Pilih --</option>
                                @foreach (['Islam', 'Kristen', 'Katholik', 'Budha', 'Hindu', 'Konghucu'] as $opt)
                                    <option value="{{ $opt }}" {{ old('agama') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('agama')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pendidikan <span class="text-danger">*</span></label>
                            <select class="form-select @error('pendidikan') is-invalid @enderror" name="pendidikan" required>
                                <option value="">-- Pilih --</option>
                                @foreach (['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2'] as $opt)
                                    <option value="{{ $opt }}" {{ old('pendidikan') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('pendidikan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Pernikahan <span class="text-danger">*</span></label>
                            <select class="form-select @error('status_pernikahan') is-invalid @enderror" name="status_pernikahan" required>
                                <option value="">-- Pilih --</option>
                                @foreach (['Single', 'Menikah', 'Duda', 'Janda'] as $opt)
                                    <option value="{{ $opt }}" {{ old('status_pernikahan') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('status_pernikahan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Anak</label>
                            <input type="number" min="0" class="form-control @error('jumlah_anak') is-invalid @enderror" name="jumlah_anak" value="{{ old('jumlah_anak', 0) }}">
                            @error('jumlah_anak')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Tinggal <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('alamat') is-invalid @enderror" name="alamat" placeholder="Jl. Semangat RT.011/01 No.18" value="{{ old('alamat') }}" required>
                            @error('alamat')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelurahan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kelurahan') is-invalid @enderror" name="kelurahan" value="{{ old('kelurahan') }}" required>
                            @error('kelurahan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kecamatan') is-invalid @enderror" name="kecamatan" value="{{ old('kecamatan') }}" required>
                            @error('kecamatan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kota/Kabupaten <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kota') is-invalid @enderror" name="kota" value="{{ old('kota') }}" required>
                            @error('kota')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Propinsi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('propinsi') is-invalid @enderror" name="propinsi" value="{{ old('propinsi') }}" required>
                            @error('propinsi')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Tempat Tinggal <span class="text-danger">*</span></label>
                            <select class="form-select @error('status_tempat_tinggal') is-invalid @enderror" name="status_tempat_tinggal" required>
                                <option value="">-- Pilih --</option>
                                @foreach (['Rumah sendiri', 'Sewa', 'Kontrak', 'Ikut Orang Tua'] as $opt)
                                    <option value="{{ $opt }}" {{ old('status_tempat_tinggal') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('status_tempat_tinggal')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. HP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ old('no_hp') }}" required>
                            @error('no_hp')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. KK <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('no_kk') is-invalid @enderror" name="no_kk" value="{{ old('no_kk') }}" required>
                            @error('no_kk')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                            @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. BPJS Ketenagakerjaan</label>
                            <input type="text" class="form-control @error('no_bpjs_tk') is-invalid @enderror" name="no_bpjs_tk" value="{{ old('no_bpjs_tk') }}">
                            @error('no_bpjs_tk')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. BPJS Kesehatan</label>
                            <input type="text" class="form-control @error('no_bpjs_kesehatan') is-invalid @enderror" name="no_bpjs_kesehatan" value="{{ old('no_bpjs_kesehatan') }}">
                            @error('no_bpjs_kesehatan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan Lain Biodata</label>
                            <textarea class="form-control @error('keterangan_biodata') is-invalid @enderror" name="keterangan_biodata" rows="2">{{ old('keterangan_biodata') }}</textarea>
                            @error('keterangan_biodata')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- ============ B. EMERGENCY CONTACT ============ --}}
                    <h6 class="section-title">B. Emergency Contact (Tidak Serumah)</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ec_nama') is-invalid @enderror" name="ec_nama" value="{{ old('ec_nama') }}" required>
                            @error('ec_nama')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tempat Tinggal <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ec_alamat') is-invalid @enderror" name="ec_alamat" value="{{ old('ec_alamat') }}" required>
                            @error('ec_alamat')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. HP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ec_no_hp') is-invalid @enderror" name="ec_no_hp" value="{{ old('ec_no_hp') }}" required>
                            @error('ec_no_hp')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hubungan <span class="text-danger">*</span></label>
                            <select class="form-select @error('ec_hubungan') is-invalid @enderror" name="ec_hubungan" required>
                                <option value="">-- Pilih --</option>
                                @foreach (['Orang tua', 'Pasangan', 'Kakak', 'Adik', 'Saudara', 'Anak'] as $opt)
                                    <option value="{{ $opt }}" {{ old('ec_hubungan') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('ec_hubungan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- ============ C. BANKING ============ --}}
                    <h6 class="section-title">C. Banking</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nama Bank</label>
                            <input type="text" class="form-control @error('nama_bank') is-invalid @enderror" name="nama_bank" placeholder="contoh: BCA" value="{{ old('nama_bank') }}">
                            @error('nama_bank')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No. Rekening</label>
                            <input type="text" class="form-control @error('no_rekening') is-invalid @enderror" name="no_rekening" value="{{ old('no_rekening') }}">
                            @error('no_rekening')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Pemilik Rekening</label>
                            <input type="text" class="form-control @error('nama_di_rekening') is-invalid @enderror" name="nama_di_rekening" value="{{ old('nama_di_rekening') }}">
                            @error('nama_di_rekening')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- ============ D. FOTO WAJAH ============ --}}
                    <h6 class="section-title">D. Foto Wajah</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Foto Wajah <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('foto') is-invalid @enderror" name="foto" accept="image/*" required>
                            <small class="form-text text-muted">Semua format gambar diterima (JPG/PNG/WebP/BMP), maksimal 10 MB &mdash; otomatis dikonversi ke JPG terkompresi</small>
                            @error('foto')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- ============ E. PENEMPATAN KERJA ============ --}}
                    <h6 class="section-title">E. Penempatan Kerja</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Customer</label>
                            <input type="text" class="form-control @error('nama_customer') is-invalid @enderror" name="nama_customer" value="{{ old('nama_customer') }}">
                            @error('nama_customer')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode Vendor</label>
                            <input type="text" class="form-control @error('kode_vendor') is-invalid @enderror" name="kode_vendor" value="{{ old('kode_vendor') }}">
                            @error('kode_vendor')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan</label>
                            <select class="form-select @error('posisi') is-invalid @enderror" name="posisi">
                                <option value="">-- Pilih Posisi --</option>
                                <optgroup label="ADMIN/PIC Roles">
                                    @foreach (config('positions.admin_pic_roles', []) as $role)
                                        <option value="{{ $role }}" {{ old('posisi') == $role ? 'selected' : '' }}>{{ $role }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Posisi Operasional">
                                    @foreach (config('positions.operational_positions', []) as $pos)
                                        <option value="{{ $pos }}" {{ old('posisi') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('posisi')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type Lokasi</label>
                            <select class="form-select @error('type_lokasi') is-invalid @enderror" name="type_lokasi">
                                <option value="">-- Pilih --</option>
                                @foreach (['Cabang', 'DC', 'CP', 'HO'] as $opt)
                                    <option value="{{ $opt }}" {{ old('type_lokasi') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('type_lokasi')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi Kerja</label>
                            <input type="text" class="form-control @error('penempatan') is-invalid @enderror" name="penempatan" value="{{ old('penempatan') }}">
                            @error('penempatan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Area Kerja</label>
                            <input type="text" class="form-control @error('area_kerja') is-invalid @enderror" name="area_kerja" value="{{ old('area_kerja') }}">
                            @error('area_kerja')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Masuk</label>
                            <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}">
                            @error('tanggal_masuk')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" name="status">
                                @foreach (['Aktif', 'Training', 'Resign', 'Cancel', 'Fraud'] as $opt)
                                    <option value="{{ $opt }}" {{ old('status', 'Aktif') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('status')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Keluar</label>
                            <input type="date" class="form-control @error('tanggal_keluar') is-invalid @enderror" name="tanggal_keluar" value="{{ old('tanggal_keluar') }}">
                            @error('tanggal_keluar')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Perpanjangan Terakhir</label>
                            <input type="date" class="form-control @error('tanggal_perpanjangan_terakhir') is-invalid @enderror" name="tanggal_perpanjangan_terakhir" value="{{ old('tanggal_perpanjangan_terakhir') }}">
                            @error('tanggal_perpanjangan_terakhir')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. PKS Masuk</label>
                            <input type="text" class="form-control @error('no_pks_masuk') is-invalid @enderror" name="no_pks_masuk" value="{{ old('no_pks_masuk') }}">
                            @error('no_pks_masuk')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. PKS Perpanjangan</label>
                            <input type="text" class="form-control @error('no_pks_perpanjangan') is-invalid @enderror" name="no_pks_perpanjangan" value="{{ old('no_pks_perpanjangan') }}">
                            @error('no_pks_perpanjangan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan Perpanjangan</label>
                            <textarea class="form-control @error('keterangan_perpanjangan') is-invalid @enderror" name="keterangan_perpanjangan" rows="2">{{ old('keterangan_perpanjangan') }}</textarea>
                            @error('keterangan_perpanjangan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan Lain-lain</label>
                            <textarea class="form-control @error('note1') is-invalid @enderror" name="note1" rows="2">{{ old('note1') }}</textarea>
                            @error('note1')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Perekrut / Referensi</label>
                            <input type="text" class="form-control @error('nama_perekrut') is-invalid @enderror" name="nama_perekrut" placeholder="Nama yang merekomendasikan / merekrut" value="{{ old('nama_perekrut') }}">
                            @error('nama_perekrut')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <a href="{{ route('employee.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                        </form>
                    </div>

                    <!-- Paste Otomatis Tab -->
                    <div class="tab-pane fade" id="paste" role="tabpanel">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle"></i>
                            Paste teks dari document atau chat, sistem akan otomatis extract dan isi field-field form. Field yang tidak terisi dari teks tetap perlu Anda lengkapi di tab Input Manual.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tempel Data Karyawan</label>
                            <textarea id="pasteArea" class="form-control" rows="10" placeholder="Contoh:&#10;NIK KTP : 3201010407910007&#10;NAMA SESUAI KTP : Adi Zulhadi&#10;TEMPAT LAHIR : Bogor&#10;TANGGAL LAHIR : 1991-07-04&#10;NO HP : 081234567890&#10;NAMA BANK : BCA&#10;NO REKENING : 6830295338&#10;..."></textarea>
                            <small class="form-text text-muted">Format akan dikenali otomatis dari teks</small>
                        </div>

                        <div id="pasteResult" class="alert alert-success d-none">
                            <h6>Data berhasil diextract:</h6>
                            <table class="table table-sm mb-0">
                                <tbody id="pasteTable"></tbody>
                            </table>
                            <small class="text-muted d-block mt-2">Catatan: Foto wajah & field wajib lain yang tidak ada di teks harus diisi lewat tab Input Manual.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" id="parseBtn" class="btn btn-primary">
                                <i class="bi bi-lightning-charge"></i> Parse & Isi Form Manual
                            </button>
                            <button type="button" id="clearPasteBtn" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
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

    .section-title {
        margin-top: 1.75rem;
        margin-bottom: 0.75rem;
        padding-bottom: 0.4rem;
        border-bottom: 2px solid #FF6B35;
        color: #FF6B35;
        font-weight: 600;
    }
    .section-title:first-of-type { margin-top: 0; }
</style>

<script>
    // ===== PASTE OTOMATIS Functions =====
    const pasteArea = document.getElementById('pasteArea');
    const parseBtn = document.getElementById('parseBtn');
    const clearPasteBtn = document.getElementById('clearPasteBtn');
    const pasteResult = document.getElementById('pasteResult');
    const pasteTable = document.getElementById('pasteTable');

    // Mapping fields dari teks ke nama field form (key sudah UPPERCASE)
    const fieldMapping = {
        'NAMA SESUAI KTP': 'nama_ktp',
        'NAMA KTP': 'nama_ktp',
        'NAMA LENGKAP': 'nama_ktp',
        'NIK KTP': 'nik',
        'NIK': 'nik',
        'TEMPAT LAHIR': 'tempat_lahir',
        'TANGGAL LAHIR': 'tanggal_lahir',
        'JENIS KELAMIN': 'jenis_kelamin',
        'AGAMA': 'agama',
        'PENDIDIKAN': 'pendidikan',
        'STATUS PERNIKAHAN': 'status_pernikahan',
        'JUMLAH ANAK': 'jumlah_anak',
        'ALAMAT TINGGAL': 'alamat',
        'ALAMAT': 'alamat',
        'KELURAHAN': 'kelurahan',
        'KECAMATAN': 'kecamatan',
        'KOTA/KABUPATEN': 'kota',
        'KOTA': 'kota',
        'KABUPATEN': 'kota',
        'PROPINSI': 'propinsi',
        'PROVINSI': 'propinsi',
        'STATUS TEMPAT TINGGAL': 'status_tempat_tinggal',
        'NO HP': 'no_hp',
        'NO. HP': 'no_hp',
        'NO KK': 'no_kk',
        'NO. KK': 'no_kk',
        'E-MAIL': 'email',
        'EMAIL': 'email',
        'NO BPJS KETENAGAKERJAAN': 'no_bpjs_tk',
        'BPJS KETENAGAKERJAAN': 'no_bpjs_tk',
        'NO BPJS KESEHATAN': 'no_bpjs_kesehatan',
        'BPJS KESEHATAN': 'no_bpjs_kesehatan',
        'KETERANGAN LAIN BIODATA': 'keterangan_biodata',
        'NOMOR REKENING': 'no_rekening',
        'NO REKENING': 'no_rekening',
        'NAMA PEMILIK REKENING': 'nama_di_rekening',
        'NAMA DI REKENING': 'nama_di_rekening',
        'NAMA PEMILIK': 'nama_di_rekening',
        'NAMA BANK': 'nama_bank',
        'NAMA CUSTOMER': 'nama_customer',
        'POSISI LOWONGAN': 'posisi',
        'POSISI': 'posisi',
        'JABATAN': 'posisi',
        'LOKASI KERJA': 'penempatan',
        'PENEMPATAN': 'penempatan',
        'KODE VENDOR': 'kode_vendor',
        'TYPE LOKASI': 'type_lokasi',
        'AREA KERJA': 'area_kerja',
        'TANGGAL MASUK': 'tanggal_masuk',
        'TANGGAL KELUAR': 'tanggal_keluar',
        'TANGGAL PERPANJANGAN TERAKHIR': 'tanggal_perpanjangan_terakhir',
        'KETERANGAN PERPANJANGAN': 'keterangan_perpanjangan',
        'NO PKS MASUK': 'no_pks_masuk',
        'NO PKS PERPANJANGAN': 'no_pks_perpanjangan',
        'KETERANGAN LAIN-LAIN': 'note1',
        'STATUS': 'status',
        'CATATAN': 'note1',
        'NAMA PEREKRUT': 'nama_perekrut',
        'PEREKRUT': 'nama_perekrut',
        'REFERENSI': 'nama_perekrut',
        'NIK ENLULU': 'nik_enlulu',
        'NIK SEMENTARA ENLULU': 'nik_enlulu',
        'NIK OS': 'nik_os',
        'NIK OUTSOURCING': 'nik_os',
        'NIK CLIENT': 'nik_os',
    };

    const displayMapping = {
        'nama_ktp': 'Nama KTP', 'nik': 'NIK', 'tempat_lahir': 'Tempat Lahir',
        'tanggal_lahir': 'Tanggal Lahir', 'jenis_kelamin': 'Jenis Kelamin', 'agama': 'Agama',
        'pendidikan': 'Pendidikan', 'status_pernikahan': 'Status Pernikahan', 'jumlah_anak': 'Jumlah Anak',
        'alamat': 'Alamat Tinggal', 'kelurahan': 'Kelurahan', 'kecamatan': 'Kecamatan', 'kota': 'Kota/Kabupaten',
        'propinsi': 'Propinsi', 'status_tempat_tinggal': 'Status Tempat Tinggal', 'no_hp': 'No. HP',
        'no_kk': 'No. KK', 'email': 'E-mail', 'no_bpjs_tk': 'BPJS Ketenagakerjaan',
        'no_bpjs_kesehatan': 'BPJS Kesehatan', 'keterangan_biodata': 'Keterangan Biodata',
        'no_rekening': 'No. Rekening', 'nama_bank': 'Nama Bank', 'nama_di_rekening': 'Nama di Rekening',
        'nama_customer': 'Nama Customer', 'posisi': 'Jabatan', 'penempatan': 'Lokasi Kerja',
        'kode_vendor': 'Kode Vendor', 'type_lokasi': 'Type Lokasi', 'area_kerja': 'Area Kerja',
        'tanggal_masuk': 'Tanggal Masuk', 'tanggal_keluar': 'Tanggal Keluar',
        'tanggal_perpanjangan_terakhir': 'Tgl Perpanjangan Terakhir', 'keterangan_perpanjangan': 'Keterangan Perpanjangan',
        'no_pks_masuk': 'No. PKS Masuk', 'no_pks_perpanjangan': 'No. PKS Perpanjangan',
        'status': 'Status', 'note1': 'Keterangan Lain-lain',
        'nama_perekrut': 'Nama Perekrut / Referensi',
        'nik_enlulu': 'NIK ENLULU', 'nik_os': 'NIK OS'
    };

    // Parse teks dan extract field
    function parseText(text) {
        const extracted = {};
        const lines = text.split('\n');

        lines.forEach(line => {
            const match = line.match(/^([^:]+)\s*:\s*(.+)$/);
            if (match) {
                let key = match[1].trim().toUpperCase();
                let value = match[2].trim();

                // Sort mapping by key length DESC agar match yang paling spesifik dulu
                const sortedMappings = Object.entries(fieldMapping)
                    .sort((a, b) => b[0].length - a[0].length);

                for (let [mapKey, fieldName] of sortedMappings) {
                    if (key === mapKey || key.includes(mapKey)) {
                        if (extracted[fieldName] === undefined) {
                            extracted[fieldName] = value;
                        }
                        break;
                    }
                }
            }
        });

        return extracted;
    }

    // Isi field form manual berdasarkan data extracted
    function fillManualForm(extracted) {
        for (let [fieldName, value] of Object.entries(extracted)) {
            const el = document.querySelector(`#manualForm [name="${fieldName}"]`);
            if (el && value) {
                el.value = value;
            }
        }
    }

    parseBtn.addEventListener('click', function() {
        const text = pasteArea.value.trim();
        if (!text) {
            alert('Silakan paste data terlebih dahulu');
            return;
        }

        const extracted = parseText(text);
        if (Object.keys(extracted).length === 0) {
            alert('Tidak menemukan data yang valid. Format harus: KEY : VALUE');
            return;
        }

        // Tampilkan hasil extract
        pasteTable.innerHTML = '';
        for (let [fieldName, value] of Object.entries(extracted)) {
            if (value) {
                const displayName = displayMapping[fieldName] || fieldName;
                pasteTable.innerHTML += `<tr><td><strong>${displayName}:</strong></td><td>${escapeHtml(value)}</td></tr>`;
            }
        }

        // Isi form manual & pindahkan user ke tab manual
        fillManualForm(extracted);

        pasteResult.classList.remove('d-none');
        pasteResult.scrollIntoView({ behavior: 'smooth', block: 'center' });

        const manualTab = document.getElementById('manual-tab');
        if (manualTab) {
            new bootstrap.Tab(manualTab).show();
        }
    });

    clearPasteBtn.addEventListener('click', function() {
        pasteArea.value = '';
        pasteResult.classList.add('d-none');
    });

    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
</script>

@endsection
