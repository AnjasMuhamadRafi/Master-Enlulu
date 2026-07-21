<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Data Karyawan - PT Enlulu Sukses Makmur</title>
    <link rel="icon" type="image/png" href="{{ asset('images/public/ENLULU.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --enlulu-orange: #f46b35; --enlulu-dark: #202124; }
        body { background: #f4f5f7; color: var(--enlulu-dark); font-family: "Segoe UI", Arial, sans-serif; }
        .public-header { background: var(--enlulu-dark); border-bottom: 4px solid var(--enlulu-orange); }
        .brand-logo { width: 70px; height: auto; }
        .form-shell { max-width: 980px; }
        .form-card { border: 1px solid #e0e2e5; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.06); }
        .section-title { font-size: 1rem; font-weight: 700; border-bottom: 2px solid #eceef0; padding-bottom: .65rem; margin: 1.75rem 0 1rem; }
        .section-title:first-child { margin-top: 0; }
        .form-label { font-weight: 600; font-size: .9rem; }
        .required::after { content: " *"; color: #dc3545; }
        .btn-submit { background: var(--enlulu-orange); border-color: var(--enlulu-orange); color: #fff; }
        .btn-submit:hover { background: #d95a28; border-color: #d95a28; color: #fff; }
        .website-field { position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden; }
    </style>
</head>
<body>
    <header class="public-header py-3">
        <div class="container form-shell d-flex align-items-center gap-3">
            <img src="{{ asset('images/public/ENLULU.png') }}" class="brand-logo" alt="Enlulu">
            <div class="text-white">
                <h1 class="h5 mb-1">PT Enlulu Sukses Makmur</h1>
                <p class="mb-0 text-white-50">Form Kelengkapan Data Karyawan</p>
            </div>
        </div>
    </header>

    <main class="container form-shell py-4 py-md-5">
        <div class="card form-card">
            <div class="card-body p-3 p-md-5">
                <div class="alert alert-info">
                    Silakan lengkapi data berikut dengan benar. Semua field dan dokumen wajib diisi.
                </div>

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

                <form method="POST" action="{{ route('public.candidate-registration.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="website-field" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <h2 class="section-title">Data Pribadi</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Nama Lengkap</label>
                            <input type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">NIK KTP</label>
                            <input type="text" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" class="form-control" name="nik_ktp" value="{{ old('nik_ktp') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">No. HP</label>
                            <input type="tel" class="form-control" name="phone" value="{{ old('phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Tempat Lahir</label>
                            <input type="text" class="form-control" name="birth_place" value="{{ old('birth_place') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Tanggal Lahir</label>
                            <input type="date" class="form-control" name="birth_date" value="{{ old('birth_date') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Nama Ibu Kandung</label>
                            <input type="text" class="form-control" name="mother_name" value="{{ old('mother_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Lokasi Kerja</label>
                            <input type="text" class="form-control" name="work_location" value="{{ old('work_location') }}" required>
                        </div>
                    </div>

                    <h2 class="section-title">Data Rekening</h2>
                    <div class="alert alert-warning py-2">
                        <i class="bi bi-exclamation-triangle"></i>
                        Rekening wajib menggunakan rekening pribadi. Tidak boleh meminjam rekening orang lain.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Bank</label>
                            <input type="text" class="form-control" name="bank_name" value="{{ old('bank_name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">No. Rekening</label>
                            <input type="text" class="form-control" name="bank_account_number" value="{{ old('bank_account_number') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">A.N. Pemilik Rekening</label>
                            <input type="text" class="form-control" name="bank_account_holder" value="{{ old('bank_account_holder') }}" required>
                        </div>
                    </div>

                    <h2 class="section-title">Dokumen</h2>
                    <div class="row g-3">
                        @foreach([
                            ['ktp', 'Foto KTP', 'jpg,jpeg,png,pdf,doc,docx'],
                            ['kk', 'Foto KK', 'jpg,jpeg,png,pdf,doc,docx'],
                            ['diploma', 'Ijazah', 'jpg,jpeg,png,pdf,doc,docx'],
                            ['cv_application', 'CV & Surat Lamaran (1 file)', 'jpg,jpeg,png,pdf,doc,docx'],
                        ] as [$name, $label, $accept])
                            <div class="col-md-6">
                                <label class="form-label required">{{ $label }}</label>
                                <input type="file" class="form-control" name="{{ $name }}" accept=".{{ str_replace(',', ',.', $accept) }}" required>
                                <div class="form-text">Format: {{ strtoupper(str_replace(',', ', ', $accept)) }}. Maksimal 15 MB sebelum dioptimasi.</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="personal_account_confirmation" value="1" id="personal-account" required>
                        <label class="form-check-label" for="personal-account">
                            Saya memastikan rekening yang saya cantumkan adalah rekening pribadi atas nama saya sendiri.
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="consent" value="1" id="consent" required>
                        <label class="form-check-label" for="consent">
                            Saya menyatakan data dan dokumen yang dikirim benar serta menyetujui penggunaannya untuk proses administrasi ketenagakerjaan.
                        </label>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-submit px-4 py-2">
                            <i class="bi bi-send"></i> Kirim Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
