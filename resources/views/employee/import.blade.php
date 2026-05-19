@extends('layouts.app')

@section('title', 'Import Karyawan')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 text-dark"><i class="bi bi-file-earmark-spreadsheet" style="color: #FF6B35; margin-right: 8px;"></i> Import Data Karyawan</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">Upload File CSV atau Excel</h5>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- STEP 1: Upload File --}}
                <div id="uploadStep">
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4 p-4 border-2 border-dashed rounded text-center" id="dropZone" style="border-color: #FF6B35; cursor: pointer; transition: all 0.3s;">
                            <div class="mb-3">
                                <i class="bi bi-cloud-arrow-up" style="font-size: 3rem; color: #FF6B35;"></i>
                            </div>
                            <h5>Drag dan drop file di sini</h5>
                            <p class="text-muted">atau</p>
                            <input type="file" id="fileInput" name="file" class="form-control" accept=".csv,.txt,.xlsx,.xls" style="display: none;">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click();">
                                <i class="bi bi-folder2-open"></i> Pilih File
                            </button>
                            <p class="small text-muted mt-3">Format: CSV atau Excel (.csv, .xlsx, .xls) | Ukuran maksimal: 5MB</p>
                        </div>

                        <div id="fileInfo" class="mb-3" style="display: none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>File terpilih:</strong> <span id="fileName"></span>
                            </div>
                        </div>

                        <div class="alert alert-info mb-4">
                            <h6 class="mb-3"><i class="bi bi-info-circle"></i> Petunjuk Format File</h6>
                            <ul class="mb-0 small">
                                <li><strong>Kolom yang diperlukan:</strong></li>
                                <li style="margin-top: 8px;">NIK KTP, NAMA KTP/NAMA, KODE VENDOR, POSISI/JABATAN, PENEMPATAN/LOKASI, TYPE LOKASI, AREA KERJA, NO REKENING, NAMA BANK, NAMA REKENING, STATUS, NOTE</li>
                                <li style="margin-top: 8px;">Data yang sudah ada akan diupdate otomatis berdasarkan NIK KTP</li>
                                <li>Gunakan template Excel yang sudah diformat untuk kemudahan input</li>
                                <li>Pastikan file tidak memiliki baris kosong di atas header</li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="validateBtn" disabled>
                                <i class="bi bi-check-circle"></i> Validasi & Preview
                            </button>
                            <a href="{{ route('employee.download-template') }}" class="btn btn-secondary">
                                <i class="bi bi-download"></i> Download Template
                            </a>
                            <a href="{{ route('employee.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>

                {{-- STEP 2: Preview Data --}}
                <div id="previewStep" style="display: none;">
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-info-circle"></i> <strong>Preview</strong> - Periksa data yang akan ditambah atau diubah sebelum mengkonfirmasi
                    </div>

                    {{-- Summary Stats --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div style="background: #d4edda; padding: 15px; border-radius: 5px; text-align: center;">
                                <p style="color: #999; font-size: 11px; margin: 0;"><strong>DATA DITAMBAH</strong></p>
                                <p style="font-size: 24px; font-weight: bold; margin: 8px 0 0 0; color: #155724;" id="createdCount">0</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background: #cfe2ff; padding: 15px; border-radius: 5px; text-align: center;">
                                <p style="color: #999; font-size: 11px; margin: 0;"><strong>DATA DIUPDATE</strong></p>
                                <p style="font-size: 24px; font-weight: bold; margin: 8px 0 0 0; color: #004085;" id="updatedCount">0</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background: #f8d7da; padding: 15px; border-radius: 5px; text-align: center;">
                                <p style="color: #999; font-size: 11px; margin: 0;"><strong>ERROR</strong></p>
                                <p style="font-size: 24px; font-weight: bold; margin: 8px 0 0 0; color: #721c24;" id="errorCount">0</p>
                            </div>
                        </div>
                    </div>

                    {{-- Debug Info: Detected Columns --}}
                    <div class="alert alert-info mb-4" id="debugColumnsSection" style="display: none; font-size: 0.85rem;">
                        <small><strong>Kolom yang Terdeteksi:</strong></small>
                        <p id="debugColumns" style="margin: 5px 0 0 0; font-family: monospace; font-size: 0.8rem;"></p>
                    </div>

                    {{-- Created Data --}}
                    <div id="createdSection" style="display: none; margin-bottom: 25px;">
                        <h6 style="color: #155724; margin-bottom: 12px;">✓ DATA YANG AKAN DITAMBAH</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr style="background: #d4edda;">
                                        <th>NIK KTP</th>
                                        <th>Nama</th>
                                        <th>Kode Vendor</th>
                                        <th>Posisi</th>
                                        <th>Type Lokasi</th>
                                        <th>Penempatan</th>
                                        <th>Area Kerja</th>
                                        <th>Bank</th>
                                        <th>No Rekening</th>
                                        <th>Nama Rekening</th>
                                        <th>Status</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody id="createdTable">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Updated Data --}}
                    <div id="updatedSection" style="display: none; margin-bottom: 25px;">
                        <h6 style="color: #004085; margin-bottom: 12px;">↻ DATA YANG AKAN DIUPDATE</h6>
                        <div id="updatedTable"></div>
                    </div>

                    {{-- Errors --}}
                    <div id="errorSection" style="display: none; margin-bottom: 25px;">
                        <h6 style="color: #721c24; margin-bottom: 12px;">⚠ ERROR</h6>
                        <div class="alert alert-danger" style="font-size: 0.9rem;">
                            <ul class="mb-0" id="errorTable">
                            </ul>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" id="confirmBtn">
                            <i class="bi bi-check-circle"></i> Konfirmasi & Proses Import
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn">
                            <i class="bi bi-arrow-left"></i> Batal
                        </button>
                    </div>
                </div>

                {{-- STEP 3: Processing --}}
                <div id="processingStep" style="display: none;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5>Sedang memproses import...</h5>
                        <p class="text-muted">Mohon tunggu, jangan tutup halaman ini</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">Informasi</h5>
            </div>
            <div class="card-body small">
                <h6 class="mb-2"><i class="bi bi-question-circle"></i> Bagaimana cara import?</h6>
                <ol class="ps-3 mb-3">
                    <li>Siapkan file Excel/CSV sesuai template</li>
                    <li>Upload file melalui form di samping</li>
                    <li>Periksa preview data</li>
                    <li>Klik "Konfirmasi & Proses Import"</li>
                </ol>

                <h6 class="mb-2"><i class="bi bi-lightbulb"></i> Tips</h6>
                <ul class="ps-3 mb-0">
                    <li>NIK KTP harus 16 angka</li>
                    <li>Data lama akan diupdate jika NIK KTP sama</li>
                    <li>Periksa error sebelum import</li>
                    <li>Download template untuk format yang benar</li>
                </ul>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    const validateBtn = document.getElementById('validateBtn');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const uploadStep = document.getElementById('uploadStep');
    const previewStep = document.getElementById('previewStep');
    const processingStep = document.getElementById('processingStep');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const uploadForm = document.getElementById('uploadForm');

    let currentPreviewData = null;

    // File upload handling
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            fileName.textContent = this.files[0].name;
            fileInfo.style.display = 'block';
            validateBtn.disabled = false;
        }
    });

    // Drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.backgroundColor = '#fff0e6';
        this.style.borderColor = '#FF6B35';
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.backgroundColor = 'transparent';
        this.style.borderColor = '#FF6B35';
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.backgroundColor = 'transparent';
        fileInput.files = e.dataTransfer.files;
        if (fileInput.files.length > 0) {
            fileName.textContent = fileInput.files[0].name;
            fileInfo.style.display = 'block';
            validateBtn.disabled = false;
        }
    });

    // Validate button
    validateBtn.addEventListener('click', async function() {
        if (!fileInput.files.length) {
            alert('Pilih file terlebih dahulu');
            return;
        }

        // Show loading
        validateBtn.disabled = true;
        const originalText = validateBtn.innerHTML;
        validateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sedang validasi...';

        const formData = new FormData(uploadForm);

        try {
            const response = await fetch('{{ route("employee.import-validate") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Response text:', text);
                throw new Error('Server returned non-JSON response');
            }

            const data = await response.json();

            if (!data.success) {
                alert('Error: ' + (data.error || data.message || 'Validasi gagal'));
                return;
            }

            // Store preview data
            currentPreviewData = data.preview;

            // Update counts
            document.getElementById('createdCount').textContent = data.preview.created.length;
            document.getElementById('updatedCount').textContent = data.preview.updated.length;
            document.getElementById('errorCount').textContent = data.preview.errors.length;

            // Display debug info about detected columns
            if (data.columnMapping && data.headers) {
                const mappingInfo = Object.entries(data.columnMapping)
                    .filter(([, idx]) => idx !== null)
                    .map(([field, idx]) => `${field}: "${data.headers[idx]}"`)
                    .join(', ');
                
                if (mappingInfo) {
                    document.getElementById('debugColumnsSection').style.display = 'block';
                    document.getElementById('debugColumns').textContent = mappingInfo;
                }
            }

            // Populate created table
            if (data.preview.created.length > 0) {
                document.getElementById('createdSection').style.display = 'block';
                const tbody = document.getElementById('createdTable');
                tbody.innerHTML = '';
                data.preview.created.forEach(item => {
                    const row = `<tr>
                        <td><code>${item.nik}</code></td>
                        <td><strong>${item.nama}</strong></td>
                        <td>${item.kode_vendor || '-'}</td>
                        <td>${item.posisi || '-'}</td>
                        <td>${item.type_lokasi || '-'}</td>
                        <td>${item.penempatan || '-'}</td>
                        <td>${item.area_kerja || '-'}</td>
                        <td>${item.nama_bank || '-'}</td>
                        <td>${item.no_rekening || '-'}</td>
                        <td>${item.nama_di_rekening || '-'}</td>
                        <td><span class="badge bg-success">${item.status}</span></td>
                        <td>${item.note1 || '-'}</td>
                    </tr>`;
                    tbody.innerHTML += row;
                });
            } else {
                document.getElementById('createdSection').style.display = 'none';
            }

            // Populate updated table
            if (data.preview.updated.length > 0) {
                document.getElementById('updatedSection').style.display = 'block';
                const updatedDiv = document.getElementById('updatedTable');
                updatedDiv.innerHTML = '';
                data.preview.updated.forEach(item => {
                    let changesHtml = '<ul style="list-style: none; padding: 0; margin: 0;">';
                    for (const [field, change] of Object.entries(item.changes)) {
                        changesHtml += `<li style="margin: 4px 0; font-size: 0.9rem;">
                            <strong>${field}:</strong><br>
                            <code style="color: #dc3545;">${change.old}</code> 
                            <i class="bi bi-arrow-right"></i> 
                            <code style="color: #198754;">${change.new}</code>
                        </li>`;
                    }
                    changesHtml += '</ul>';

                    const itemHtml = `<div class="card mb-2">
                        <div class="card-body p-2">
                            <div style="margin-bottom: 8px;">
                                <strong><code>${item.nik}</code> - ${item.nama}</strong>
                            </div>
                            ${changesHtml}
                        </div>
                    </div>`;
                    updatedDiv.innerHTML += itemHtml;
                });
            } else {
                document.getElementById('updatedSection').style.display = 'none';
            }

            // Populate errors
            if (data.preview.errors.length > 0) {
                document.getElementById('errorSection').style.display = 'block';
                const errorTable = document.getElementById('errorTable');
                errorTable.innerHTML = '';
                data.preview.errors.forEach(error => {
                    errorTable.innerHTML += `<li>${error}</li>`;
                });
            } else {
                document.getElementById('errorSection').style.display = 'none';
            }

            // Show preview step
            uploadStep.style.display = 'none';
            previewStep.style.display = 'block';

        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            // Reset button state
            validateBtn.disabled = false;
            validateBtn.innerHTML = originalText;
        }
    });

    // Confirm button
    confirmBtn.addEventListener('click', function() {
        if (currentPreviewData.errors.length > 0) {
            if (!confirm('Ada ' + currentPreviewData.errors.length + ' error(s). Lanjutkan import?')) {
                return;
            }
        }

        // Create FormData with file
        const formData = new FormData();
        // Get CSRF token from the hidden input in the form
        const tokenInput = document.querySelector('input[name="_token"]');
        if (tokenInput) {
            formData.append('_token', tokenInput.value);
        }
        formData.append('file', fileInput.files[0]);

        // Submit using fetch for better control
        processingStep.style.display = 'block';
        previewStep.style.display = 'none';

        fetch('{{ route("employee.import-process") }}', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Server response:', text.substring(0, 500));
                throw new Error('Server returned invalid response (status: ' + response.status + ')');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success and redirect
                window.location.href = data.redirect;
            } else {
                throw new Error(data.message || 'Import failed');
            }
        })
        .catch(error => {
            console.error('Import error:', error);
            alert('Error: ' + error.message);
            processingStep.style.display = 'none';
            previewStep.style.display = 'block';
        });
    });

    // Cancel button
    cancelBtn.addEventListener('click', function() {
        currentPreviewData = null;
        previewStep.style.display = 'none';
        uploadStep.style.display = 'block';
        fileInput.value = '';
        fileInfo.style.display = 'none';
        validateBtn.disabled = true;
    });
});
</script>

<style>
    .page-header {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 140, 74, 0.1) 100%);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #FF6B35;
    }

    #dropZone:hover {
        background-color: rgba(255, 107, 53, 0.05);
    }
</style>
@endsection
