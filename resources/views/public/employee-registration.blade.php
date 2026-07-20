<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Data Calon Karyawan - PT Enlulu Sukses Makmur</title>
    <link rel="icon" type="image/png" href="{{ asset('images/public/ENLULU.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        :root {
            --enlulu-orange: #f46b35;
            --enlulu-dark: #202124;
        }
        body {
            background: #f4f5f7;
            color: var(--enlulu-dark);
            font-family: "Segoe UI", Arial, sans-serif;
        }
        .public-header {
            background: var(--enlulu-dark);
            border-bottom: 4px solid var(--enlulu-orange);
        }
        .brand-logo {
            width: 70px;
            height: auto;
        }
        .form-shell {
            max-width: 980px;
        }
        .form-card {
            border: 1px solid #e0e2e5;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .06);
        }
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            border-bottom: 2px solid #eceef0;
            padding-bottom: .65rem;
            margin: 1.75rem 0 1rem;
        }
        .section-title:first-child {
            margin-top: 0;
        }
        .form-label {
            font-weight: 600;
            font-size: .9rem;
        }
        .required::after {
            content: " *";
            color: #dc3545;
        }
        .btn-submit {
            background: var(--enlulu-orange);
            border-color: var(--enlulu-orange);
            color: #fff;
        }
        .btn-submit:hover {
            background: #d95a28;
            border-color: #d95a28;
            color: #fff;
        }
        .website-field {
            position: absolute;
            left: -10000px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <header class="public-header py-3">
        <div class="container form-shell d-flex align-items-center gap-3">
            <img src="{{ asset('images/public/ENLULU.png') }}" class="brand-logo" alt="Enlulu">
            <div class="text-white">
                <h1 class="h5 mb-1">PT Enlulu Sukses Makmur</h1>
                <p class="mb-0 text-white-50">Form Data Calon Karyawan</p>
            </div>
        </div>
    </header>

    <main class="container form-shell py-4 py-md-5">
        <div class="card form-card">
            <div class="card-body p-3 p-md-5">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Periksa kembali data berikut:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.employee-registration.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="website-field" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <h2 class="section-title">Identitas</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">NIK KTP</label>
                            <input type="text" inputmode="numeric" pattern="[0-9]{16}" maxlength="16"
                                   class="form-control @error('nik') is-invalid @enderror"
                                   name="nik" value="{{ old('nik') }}" required>
                            <div class="form-text">Masukkan 16 angka sesuai KTP.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Nama Sesuai KTP</label>
                            <input type="text" class="form-control" name="nama_ktp" value="{{ old('nama_ktp') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Tempat Lahir</label>
                            <input type="text" class="form-control" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Tanggal Lahir</label>
                            <input type="date" class="form-control" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Jenis Kelamin</label>
                            <select class="form-select" name="jenis_kelamin" required>
                                <option value="">Pilih jenis kelamin</option>
                                <option value="Pria" {{ old('jenis_kelamin') === 'Pria' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Wanita" {{ old('jenis_kelamin') === 'Wanita' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agama</label>
                            <select class="form-select" name="agama">
                                <option value="">Pilih agama</option>
                                @foreach(['Islam', 'Kristen', 'Katholik', 'Budha', 'Hindu', 'Konghucu'] as $option)
                                    <option value="{{ $option }}" {{ old('agama') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Pernikahan</label>
                            <select class="form-select" name="status_pernikahan">
                                <option value="">Pilih status</option>
                                @foreach(['Single', 'Menikah', 'Duda', 'Janda'] as $option)
                                    <option value="{{ $option }}" {{ old('status_pernikahan') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. KK</label>
                            <input type="text" class="form-control" name="no_kk" value="{{ old('no_kk') }}">
                        </div>
                    </div>

                    <h2 class="section-title">Alamat dan Kontak</h2>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required">Alamat Lengkap</label>
                            <textarea class="form-control" name="alamat" rows="3" required>{{ old('alamat') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelurahan</label>
                            <input type="text" class="form-control" name="kelurahan" value="{{ old('kelurahan') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" class="form-control" name="kecamatan" value="{{ old('kecamatan') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kota/Kabupaten</label>
                            <input type="text" class="form-control" name="kota" value="{{ old('kota') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Provinsi</label>
                            <input type="text" class="form-control" name="propinsi" value="{{ old('propinsi') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">No. HP/WhatsApp</label>
                            <input type="tel" class="form-control" name="no_hp" value="{{ old('no_hp') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                        </div>
                    </div>

                    <h2 class="section-title">Kontak Darurat</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="ec_nama" value="{{ old('ec_nama') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No. HP</label>
                            <input type="tel" class="form-control" name="ec_no_hp" value="{{ old('ec_no_hp') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hubungan</label>
                            <input type="text" class="form-control" name="ec_hubungan" value="{{ old('ec_hubungan') }}">
                        </div>
                    </div>

                    <h2 class="section-title">Rekening Bank</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nama Bank</label>
                            <input type="text" class="form-control" name="nama_bank" value="{{ old('nama_bank') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No. Rekening</label>
                            <input type="text" class="form-control" name="no_rekening" value="{{ old('no_rekening') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Pemilik Rekening</label>
                            <input type="text" class="form-control" name="nama_di_rekening" value="{{ old('nama_di_rekening') }}">
                        </div>
                    </div>

                    <h2 class="section-title">Penempatan</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Posisi</label>
                            <select class="form-select" name="posisi">
                                <option value="">Pilih jika sudah diinformasikan</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position }}" {{ old('posisi') === $position ? 'selected' : '' }}>{{ $position }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Customer/Klien</label>
                            <input type="text" class="form-control" name="nama_customer" value="{{ old('nama_customer') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rencana Tanggal Masuk</label>
                            <input type="date" class="form-control" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Foto Wajah</label>
                            <input type="file" class="form-control" name="foto" accept="image/*">
                            <div class="form-text">Maksimal 10 MB.</div>
                        </div>
                    </div>

                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="consent" value="1" id="consent" required>
                        <label class="form-check-label" for="consent">
                            Saya menyatakan data yang diisi benar dan menyetujui penggunaannya untuk proses administrasi ketenagakerjaan.
                        </label>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-submit px-4 py-2">Kirim Data</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
