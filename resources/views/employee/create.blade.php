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
    <div class="col-lg-6">
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
                        <form method="POST" action="{{ route('employee.store') }}" id="manualForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">NIK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nik') is-invalid @enderror" 
                               name="nik" 
                               placeholder="Masukkan 16 angka NIK (contoh: 1234567890123456)" 
                               value="{{ old('nik') }}" 
                               maxlength="16"
                               pattern="[0-9]{16}"
                               title="NIK harus terdiri dari 16 angka"
                               required>
                        <small class="form-text text-muted">NIK harus terdiri dari 16 angka</small>
                        @error('nik')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama KTP<span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_ktp') is-invalid @enderror" 
                               name="nama_ktp" 
                               placeholder="Nama sesuai KTP" 
                               value="{{ old('nama_ktp') }}"
                               required>
                        @error('nama_ktp')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kode Vendor</label>
                        <input type="text" class="form-control @error('kode_vendor') is-invalid @enderror" name="kode_vendor" placeholder="Kode vendor" value="{{ old('kode_vendor') }}">
                        @error('kode_vendor')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Posisi/Jabatan</label>
                        <select class="form-select @error('posisi') is-invalid @enderror" name="posisi">
                            <option value="">-- Pilih Posisi --</option>
                            
                            <!-- ADMIN/PIC Roles -->
                            <optgroup label="ADMIN/PIC Roles">
                                @foreach (config('positions.admin_pic_roles', []) as $role)
                                    <option value="{{ $role }}" {{ old('posisi') == $role ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </optgroup>
                            
                            <!-- Operational Positions -->
                            <optgroup label="Posisi Operasional">
                                @foreach (config('positions.operational_positions', []) as $pos)
                                    <option value="{{ $pos }}" {{ old('posisi') == $pos ? 'selected' : '' }}>
                                        {{ $pos }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        @error('posisi')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi Kerja</label>
                        <input type="text" class="form-control @error('penempatan') is-invalid @enderror" name="penempatan" placeholder="Lokasi penempatan" value="{{ old('penempatan') }}">
                        @error('penempatan')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type Lokasi</label>
                        <input type="text" class="form-control @error('type_lokasi') is-invalid @enderror" name="type_lokasi" placeholder="Type lokasi" value="{{ old('type_lokasi') }}">
                        @error('type_lokasi')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Area Kerja</label>
                        <input type="text" class="form-control @error('area_kerja') is-invalid @enderror" name="area_kerja" placeholder="Area kerja" value="{{ old('area_kerja') }}">
                        @error('area_kerja')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No. Rekening</label>
                        <input type="text" class="form-control @error('no_rekening') is-invalid @enderror" name="no_rekening" placeholder="Nomor rekening" value="{{ old('no_rekening') }}">
                        @error('no_rekening')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Bank</label>
                        <input type="text" class="form-control @error('nama_bank') is-invalid @enderror" name="nama_bank" placeholder="Nama bank" value="{{ old('nama_bank') }}">
                        @error('nama_bank')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama di Rekening</label>
                        <input type="text" class="form-control @error('nama_di_rekening') is-invalid @enderror" name="nama_di_rekening" placeholder="Nama pemilik rekening" value="{{ old('nama_di_rekening') }}">
                        @error('nama_di_rekening')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Kerja</label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status">
                            <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Training" {{ old('status') == 'Training' ? 'selected' : '' }}>Training</option>
                            <option value="Resign" {{ old('status') == 'Resign' ? 'selected' : '' }}>Resign</option>
                        </select>
                        @error('status')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control @error('note1') is-invalid @enderror" name="note1" placeholder="Catatan tambahan" rows="3">{{ old('note1') }}</textarea>
                        @error('note1')<span class="invalid-feedback">{{ $message }}</span>@enderror
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
                            Paste teks dari document atau chat, sistem akan otomatis extract dan isi field-field form
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tempel Data Karyawan</label>
                            <textarea id="pasteArea" class="form-control" rows="8" placeholder="Contoh:&#10;NAMA LENGKAP : Adi Zulhadi&#10;NIK KTP : 3201010407910007&#10;NOMOR REKENING : 6830295338&#10;NAMA BANK : BCA&#10;..."></textarea>
                            <small class="form-text text-muted">Format akan dikenali otomatis dari teks</small>
                        </div>

                        <div id="pasteResult" class="alert alert-success d-none">
                            <h6>Data berhasil diextract:</h6>
                            <table class="table table-sm mb-0">
                                <tbody id="pasteTable"></tbody>
                            </table>
                        </div>

                        <form method="POST" action="{{ route('employee.store') }}" id="pasteForm">
                            @csrf
                            <div id="hiddenFields"></div>

                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" id="saveParseBtn" class="btn btn-primary" disabled>
                                    <i class="bi bi-save"></i> Simpan Data dari Parse
                                </button>
                                <a href="{{ route('employee.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>

                        <hr class="mt-5">

                        <div class="d-flex gap-2">
                            <button type="button" id="parseBtn" class="btn btn-primary">
                                <i class="bi bi-lightning-charge"></i> Parse & Isi Form
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
</style>

<script>
    // ===== PASTE OTOMATIS Functions =====
    const pasteArea = document.getElementById('pasteArea');
    const parseBtn = document.getElementById('parseBtn');
    const clearPasteBtn = document.getElementById('clearPasteBtn');
    const saveParseBtn = document.getElementById('saveParseBtn');
    const pasteResult = document.getElementById('pasteResult');
    const pasteTable = document.getElementById('pasteTable');
    const hiddenFields = document.getElementById('hiddenFields');

    // Mapping fields dari teks ke form field names
    const fieldMapping = {
        'NAMA KTP': 'nama_ktp',
        'NAMA LENGKAP': 'nama_ktp',
        'NAMA': 'nama_ktp',
        'NIK KTP': 'nik',
        'NIK': 'nik',
        'NOMOR REKENING': 'no_rekening',
        'NO REKENING': 'no_rekening',
        'NAMA DI REKENING': 'nama_di_rekening',
        'NAMA PEMILIK': 'nama_di_rekening',
        'NAMA BANK': 'nama_bank',
        'POSISI LOWONGAN': 'posisi',
        'POSISI': 'posisi',
        'JABATAN': 'posisi',
        'LOKASI KERJA': 'penempatan',
        'PENEMPATAN': 'penempatan',
        'KODE VENDOR': 'kode_vendor',
        'TYPE LOKASI': 'type_lokasi',
        'AREA KERJA': 'area_kerja',
        'STATUS': 'status',
        'CATATAN': 'note1',
    };

    // Parse teks dan extract field
    function parseText(text) {
        const extracted = {};
        
        // Split by newline
        const lines = text.split('\n');
        
        lines.forEach(line => {
            // Cari pattern "KEY : VALUE" atau "KEY: VALUE"
            const match = line.match(/^([^:]+)\s*:\s*(.+)$/);
            if (match) {
                let key = match[1].trim().toUpperCase();
                let value = match[2].trim();
                
                // Sort mapping by key length DESC untuk match yang paling spesifik dulu
                const sortedMappings = Object.entries(fieldMapping)
                    .sort((a, b) => b[0].length - a[0].length);
                
                // Cek mapping dengan yang terpanjang dulu
                for (let [mapKey, fieldName] of sortedMappings) {
                    if (key.includes(mapKey)) {
                        extracted[fieldName] = value;
                        break;
                    }
                }
            }
        });
        
        return extracted;
    }

    // Parse button clicked
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
        const displayMapping = {
            'nama_ktp': 'Nama KTP',
            'nik': 'NIK',
            'no_rekening': 'No. Rekening',
            'nama_bank': 'Nama Bank',
            'nama_di_rekening': 'Nama di Rekening',
            'posisi': 'Posisi',
            'penempatan': 'Lokasi Kerja',
            'kode_vendor': 'Kode Vendor',
            'type_lokasi': 'Type Lokasi',
            'area_kerja': 'Area Kerja',
            'status': 'Status',
            'note1': 'Catatan'
        };

        for (let [fieldName, value] of Object.entries(extracted)) {
            // Skip nama_lengkap - it will be auto-filled from nama_ktp
            if (value && fieldName !== 'nama_lengkap') {
                const displayName = displayMapping[fieldName] || fieldName;
                pasteTable.innerHTML += `
                    <tr>
                        <td><strong>${displayName}:</strong></td>
                        <td>${value}</td>
                    </tr>
                `;
            }
        }

        // Buat hidden fields untuk form submit
        hiddenFields.innerHTML = '';
        hiddenFields.innerHTML += `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
        for (let [fieldName, value] of Object.entries(extracted)) {
            // Skip nama_lengkap - it will be auto-filled from nama_ktp
            if (value && fieldName !== 'nama_lengkap') {
                hiddenFields.innerHTML += `<input type="hidden" name="${fieldName}" value="${escapeHtml(value)}">`;
            }
        }

        // Enable save button
        saveParseBtn.disabled = false;

        pasteResult.classList.remove('d-none');
        
        // Scroll to result
        pasteResult.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    // Clear button
    clearPasteBtn.addEventListener('click', function() {
        pasteArea.value = '';
        pasteResult.classList.add('d-none');
        hiddenFields.innerHTML = '';
        saveParseBtn.disabled = true;
    });

    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Auto-parse ketika user berhenti mengetik
    let parseTimeout;
    pasteArea.addEventListener('input', function() {
        clearTimeout(parseTimeout);
        parseTimeout = setTimeout(() => {
            // Optional: auto-parse, untuk sekarang user harus klik Parse button
        }, 500);
    });
</script>

@endsection
