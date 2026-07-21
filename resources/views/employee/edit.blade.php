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
    <div class="col-lg-10">
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

                <form method="POST" action="{{ route('employee.update', $employee) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- ============ A. BIODATA ============ --}}
                    <h6 class="section-title">A. Biodata</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIK KTP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nik') is-invalid @enderror" name="nik"
                                   value="{{ old('nik', $employee->nik) }}" maxlength="16" pattern="[0-9]{16}"
                                   title="NIK harus terdiri dari 16 angka" required>
                            <small class="form-text text-muted">NIK harus terdiri dari 16 angka</small>
                            @error('nik')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Sesuai KTP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_ktp') is-invalid @enderror" name="nama_ktp" value="{{ old('nama_ktp', $employee->nama_ktp) }}" required>
                            @error('nama_ktp')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Ibu Kandung</label>
                            <input type="text" class="form-control @error('nama_ibu_kandung') is-invalid @enderror" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung', $employee->nama_ibu_kandung) }}">
                            @error('nama_ibu_kandung')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK ENLULU</label>
                            <input type="text" class="form-control @error('nik_enlulu') is-invalid @enderror" name="nik_enlulu" placeholder="NIK sementara dari PT Enlulu" value="{{ old('nik_enlulu', $employee->nik_enlulu) }}">
                            <small class="form-text text-muted">NIK sementara yang diterbitkan PT Enlulu</small>
                            @error('nik_enlulu')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK OS</label>
                            <input type="text" class="form-control @error('nik_os') is-invalid @enderror" name="nik_os" placeholder="NIK dari client / outsourcing" value="{{ old('nik_os', $employee->nik_os) }}">
                            <small class="form-text text-muted">NIK yang diberikan oleh client</small>
                            @error('nik_os')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" name="tempat_lahir" value="{{ old('tempat_lahir', $employee->tempat_lahir) }}" required>
                            @error('tempat_lahir')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($employee->tanggal_lahir)->format('Y-m-d')) }}" required>
                            @error('tanggal_lahir')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select @error('jenis_kelamin') is-invalid @enderror" name="jenis_kelamin">
                                <option value="">-- Pilih --</option>
                                @foreach (['Pria', 'Wanita'] as $opt)
                                    <option value="{{ $opt }}" {{ old('jenis_kelamin', $employee->jenis_kelamin) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('jenis_kelamin')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agama</label>
                            <select class="form-select @error('agama') is-invalid @enderror" name="agama">
                                <option value="">-- Pilih --</option>
                                @foreach (['Islam', 'Kristen', 'Katholik', 'Budha', 'Hindu', 'Konghucu'] as $opt)
                                    <option value="{{ $opt }}" {{ old('agama', $employee->agama) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('agama')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pendidikan</label>
                            <select class="form-select @error('pendidikan') is-invalid @enderror" name="pendidikan">
                                <option value="">-- Pilih --</option>
                                @foreach (['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2'] as $opt)
                                    <option value="{{ $opt }}" {{ old('pendidikan', $employee->pendidikan) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('pendidikan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Pernikahan</label>
                            <select class="form-select @error('status_pernikahan') is-invalid @enderror" name="status_pernikahan">
                                <option value="">-- Pilih --</option>
                                @foreach (['Single', 'Menikah', 'Duda', 'Janda'] as $opt)
                                    <option value="{{ $opt }}" {{ old('status_pernikahan', $employee->status_pernikahan) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('status_pernikahan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Anak</label>
                            <input type="number" min="0" class="form-control @error('jumlah_anak') is-invalid @enderror" name="jumlah_anak" value="{{ old('jumlah_anak', $employee->jumlah_anak ?? 0) }}">
                            @error('jumlah_anak')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Tinggal</label>
                            <input type="text" class="form-control @error('alamat') is-invalid @enderror" name="alamat" value="{{ old('alamat', $employee->alamat) }}">
                            @error('alamat')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelurahan</label>
                            <input type="text" class="form-control @error('kelurahan') is-invalid @enderror" name="kelurahan" value="{{ old('kelurahan', $employee->kelurahan) }}">
                            @error('kelurahan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" class="form-control @error('kecamatan') is-invalid @enderror" name="kecamatan" value="{{ old('kecamatan', $employee->kecamatan) }}">
                            @error('kecamatan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kota/Kabupaten</label>
                            <input type="text" class="form-control @error('kota') is-invalid @enderror" name="kota" value="{{ old('kota', $employee->kota) }}">
                            @error('kota')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Propinsi</label>
                            <input type="text" class="form-control @error('propinsi') is-invalid @enderror" name="propinsi" value="{{ old('propinsi', $employee->propinsi) }}">
                            @error('propinsi')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Tempat Tinggal</label>
                            <select class="form-select @error('status_tempat_tinggal') is-invalid @enderror" name="status_tempat_tinggal">
                                <option value="">-- Pilih --</option>
                                @foreach (['Rumah sendiri', 'Sewa', 'Kontrak', 'Ikut Orang Tua'] as $opt)
                                    <option value="{{ $opt }}" {{ old('status_tempat_tinggal', $employee->status_tempat_tinggal) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('status_tempat_tinggal')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. HP</label>
                            <input type="text" class="form-control @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ old('no_hp', $employee->no_hp) }}">
                            @error('no_hp')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. KK</label>
                            <input type="text" class="form-control @error('no_kk') is-invalid @enderror" name="no_kk" value="{{ old('no_kk', $employee->no_kk) }}">
                            @error('no_kk')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $employee->email) }}">
                            @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. BPJS Ketenagakerjaan</label>
                            <input type="text" class="form-control @error('no_bpjs_tk') is-invalid @enderror" name="no_bpjs_tk" value="{{ old('no_bpjs_tk', $employee->no_bpjs_tk) }}">
                            @error('no_bpjs_tk')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. BPJS Kesehatan</label>
                            <input type="text" class="form-control @error('no_bpjs_kesehatan') is-invalid @enderror" name="no_bpjs_kesehatan" value="{{ old('no_bpjs_kesehatan', $employee->no_bpjs_kesehatan) }}">
                            @error('no_bpjs_kesehatan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan Lain Biodata</label>
                            <textarea class="form-control @error('keterangan_biodata') is-invalid @enderror" name="keterangan_biodata" rows="2">{{ old('keterangan_biodata', $employee->keterangan_biodata) }}</textarea>
                            @error('keterangan_biodata')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- ============ B. EMERGENCY CONTACT ============ --}}
                    <h6 class="section-title">B. Emergency Contact (Tidak Serumah)</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control @error('ec_nama') is-invalid @enderror" name="ec_nama" value="{{ old('ec_nama', $employee->ec_nama) }}">
                            @error('ec_nama')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tempat Tinggal</label>
                            <input type="text" class="form-control @error('ec_alamat') is-invalid @enderror" name="ec_alamat" value="{{ old('ec_alamat', $employee->ec_alamat) }}">
                            @error('ec_alamat')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. HP</label>
                            <input type="text" class="form-control @error('ec_no_hp') is-invalid @enderror" name="ec_no_hp" value="{{ old('ec_no_hp', $employee->ec_no_hp) }}">
                            @error('ec_no_hp')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hubungan</label>
                            <select class="form-select @error('ec_hubungan') is-invalid @enderror" name="ec_hubungan">
                                <option value="">-- Pilih --</option>
                                @foreach (['Orang tua', 'Pasangan', 'Kakak', 'Adik', 'Saudara', 'Anak'] as $opt)
                                    <option value="{{ $opt }}" {{ old('ec_hubungan', $employee->ec_hubungan) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
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
                            <input type="text" class="form-control @error('nama_bank') is-invalid @enderror" name="nama_bank" value="{{ old('nama_bank', $employee->nama_bank) }}">
                            @error('nama_bank')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No. Rekening</label>
                            <input type="text" class="form-control @error('no_rekening') is-invalid @enderror" name="no_rekening" value="{{ old('no_rekening', $employee->no_rekening) }}">
                            @error('no_rekening')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Pemilik Rekening</label>
                            <input type="text" class="form-control @error('nama_di_rekening') is-invalid @enderror" name="nama_di_rekening" value="{{ old('nama_di_rekening', $employee->nama_di_rekening) }}">
                            @error('nama_di_rekening')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- ============ D. FOTO DAN DOKUMEN ============ --}}
                    <h6 class="section-title">D. Foto dan Dokumen Karyawan</h6>
                    <div class="row g-3 align-items-center">
                        @if ($employee->foto)
                            <div class="col-auto">
                                <img src="{{ asset('storage/' . $employee->foto) }}" alt="Foto" style="width: 90px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label">Ganti Foto Wajah</label>
                            <input type="file" class="form-control @error('foto') is-invalid @enderror" name="foto" accept="image/*">
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti. Semua format gambar (JPG/PNG/WebP/BMP), maks. 10 MB &mdash; otomatis dikonversi ke JPG terkompresi</small>
                            @error('foto')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        @foreach([
                            ['file_ktp', 'KTP', 'ktp', $employee->dokumen_ktp],
                            ['file_kk', 'KK', 'kk', $employee->dokumen_kk],
                            ['file_ijazah', 'Ijazah', 'ijazah', $employee->dokumen_ijazah],
                            ['file_cv_lamaran', 'CV & Surat Lamaran', 'cv', $employee->dokumen_cv],
                        ] as [$input, $label, $documentKey, $currentPath])
                            <div class="col-md-6">
                                <label class="form-label">{{ $label }}</label>
                                @if($currentPath)
                                    <div class="mb-2">
                                        <a href="{{ route('employee.document.download', [$employee, $documentKey]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> Unduh dokumen saat ini
                                        </a>
                                    </div>
                                @else
                                    <div class="text-muted small mb-2">Belum ada dokumen.</div>
                                @endif
                                <input type="file"
                                       class="form-control @error($input) is-invalid @enderror"
                                       name="{{ $input }}"
                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                <small class="form-text text-muted">
                                    Kosongkan jika tidak ingin mengganti. JPG, PNG, PDF, DOC, atau DOCX; maksimal 15 MB sebelum optimasi.
                                </small>
                                @error($input)<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </div>
                        @endforeach
                    </div>

                    {{-- ============ E. PENEMPATAN KERJA ============ --}}
                    <h6 class="section-title">E. Penempatan Kerja</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Klien</label>
                            <input type="text" class="form-control @error('nama_customer') is-invalid @enderror" name="nama_customer" value="{{ old('nama_customer', $employee->nama_customer) }}">
                            @error('nama_customer')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode Vendor</label>
                            <input type="text" class="form-control @error('kode_vendor') is-invalid @enderror" name="kode_vendor" value="{{ old('kode_vendor', $employee->kode_vendor) }}">
                            @error('kode_vendor')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan</label>
                            <input type="text"
                                   class="form-control @error('posisi') is-invalid @enderror"
                                   name="posisi"
                                   value="{{ old('posisi', $employee->posisi) }}"
                                   list="posisi-options"
                                   placeholder="Pilih atau ketik posisi">
                            <datalist id="posisi-options">
                                @foreach ($adminPicRoles ?? config('positions.admin_pic_roles', []) as $role)
                                    <option value="{{ $role }}"></option>
                                @endforeach
                                @foreach ($operationalPositions ?? config('positions.operational_positions', []) as $pos)
                                    <option value="{{ $pos }}"></option>
                                @endforeach
                            </datalist>
                            @error('posisi')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type Lokasi</label>
                            <select class="form-select @error('type_lokasi') is-invalid @enderror" name="type_lokasi">
                                <option value="">-- Pilih --</option>
                                @foreach (['Cabang', 'DC', 'CP', 'HO'] as $opt)
                                    <option value="{{ $opt }}" {{ old('type_lokasi', $employee->type_lokasi) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('type_lokasi')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi Kerja</label>
                            <input type="text" class="form-control @error('penempatan') is-invalid @enderror" name="penempatan" value="{{ old('penempatan', $employee->penempatan) }}">
                            @error('penempatan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Area Kerja</label>
                            <input type="text" class="form-control @error('area_kerja') is-invalid @enderror" name="area_kerja" value="{{ old('area_kerja', $employee->area_kerja) }}">
                            @error('area_kerja')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Masuk</label>
                            <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror" name="tanggal_masuk" value="{{ old('tanggal_masuk', optional($employee->tanggal_masuk)->format('Y-m-d')) }}">
                            @error('tanggal_masuk')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" name="status">
                                @foreach (['Aktif', 'Training', 'Resign', 'Cancel', 'Fraud'] as $opt)
                                    <option value="{{ $opt }}" {{ old('status', $employee->status) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('status')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Keluar</label>
                            <input type="date" class="form-control @error('tanggal_keluar') is-invalid @enderror" name="tanggal_keluar" value="{{ old('tanggal_keluar', optional($employee->tanggal_keluar)->format('Y-m-d')) }}">
                            @error('tanggal_keluar')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Perpanjangan Terakhir</label>
                            <input type="date" class="form-control @error('tanggal_perpanjangan_terakhir') is-invalid @enderror" name="tanggal_perpanjangan_terakhir" value="{{ old('tanggal_perpanjangan_terakhir', optional($employee->tanggal_perpanjangan_terakhir)->format('Y-m-d')) }}">
                            @error('tanggal_perpanjangan_terakhir')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. PKS Masuk</label>
                            <input type="text" class="form-control @error('no_pks_masuk') is-invalid @enderror" name="no_pks_masuk" value="{{ old('no_pks_masuk', $employee->no_pks_masuk) }}">
                            @error('no_pks_masuk')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. PKS Perpanjangan</label>
                            <input type="text" class="form-control @error('no_pks_perpanjangan') is-invalid @enderror" name="no_pks_perpanjangan" value="{{ old('no_pks_perpanjangan', $employee->no_pks_perpanjangan) }}">
                            @error('no_pks_perpanjangan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan Perpanjangan</label>
                            <textarea class="form-control @error('keterangan_perpanjangan') is-invalid @enderror" name="keterangan_perpanjangan" rows="2">{{ old('keterangan_perpanjangan', $employee->keterangan_perpanjangan) }}</textarea>
                            @error('keterangan_perpanjangan')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan Lain-lain</label>
                            <textarea class="form-control @error('note1') is-invalid @enderror" name="note1" rows="2">{{ old('note1', $employee->note1) }}</textarea>
                            @error('note1')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Perekrut / Referensi</label>
                            <input type="text" class="form-control @error('nama_perekrut') is-invalid @enderror" name="nama_perekrut" placeholder="Nama yang merekomendasikan / merekrut" value="{{ old('nama_perekrut', $employee->nama_perekrut) }}">
                            @error('nama_perekrut')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>
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
@endsection
