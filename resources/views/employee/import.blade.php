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
                                <li><strong>Kolom wajib:</strong> NIK KTP dan NAMA KTP/NAMA SESUAI KTP</li>
                                <li style="margin-top: 8px;">Kolom lainnya boleh dikosongkan dan dapat dilengkapi melalui import berikutnya</li>
                                <li style="margin-top: 8px;">Data yang sudah ada akan diupdate otomatis berdasarkan NIK KTP</li>
                                <li>Kolom kosong tidak akan menimpa data lama</li>
                                <li>Jenis kelamin LAKI-LAKI/PEREMPUAN otomatis disimpan sebagai Pria/Wanita</li>
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

                    <div class="alert alert-secondary mb-4" id="previewTotals">
                        Total baris terisi: 0 | Valid unik: 0 | NIK duplikat: 0
                    </div>

                    <div class="alert alert-info mb-4" id="partialImportNotice" style="display: none;">
                        <i class="bi bi-info-circle"></i>
                        <strong>Import parsial aktif.</strong>
                        Baris valid tetap dapat diimpor. Baris error akan dilewati dan dapat diperbaiki pada file, lalu diimpor kembali.
                    </div>

                    {{-- Debug Info: Detected Columns --}}
                    <div class="alert alert-info mb-4" id="debugColumnsSection" style="display: none; font-size: 0.85rem;">
                        <small><strong>Kolom yang Terdeteksi:</strong></small>
                        <p id="debugColumns" style="margin: 5px 0 0 0; font-family: monospace; font-size: 0.8rem;"></p>
                    </div>

                    {{-- Created Data --}}
                    <div id="createdSection" style="display: none; margin-bottom: 25px;">
                        <h6 style="color: #155724; margin-bottom: 12px;">✓ DATA YANG AKAN DITAMBAH</h6>
                        <p class="small text-muted" id="createdPreviewLimit" style="display: none;"></p>
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
                        <p class="small text-muted" id="updatedPreviewLimit" style="display: none;"></p>
                        <div id="updatedTable"></div>
                    </div>

                    {{-- Errors --}}
                    <div id="errorSection" style="display: none; margin-bottom: 25px;">
                        <h6 style="color: #721c24; margin-bottom: 12px;">⚠ ERROR</h6>
                        <p class="small text-muted" id="errorPreviewLimit" style="display: none;"></p>
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
                        <button type="button" class="btn btn-outline-primary" id="fixFileBtn" style="display: none;">
                            <i class="bi bi-pencil-square"></i> Perbaiki File Error
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
                    <li>Nama sesuai KTP wajib diisi</li>
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
    const fixFileBtn = document.getElementById('fixFileBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const uploadForm = document.getElementById('uploadForm');

    let currentPreviewData = null;
    const previewLimit = 100;

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value == null ? '' : String(value);
        return element.innerHTML;
    }

    function setPreviewLimitMessage(elementId, shown, total) {
        const element = document.getElementById(elementId);
        if (total > shown) {
            element.textContent = `Menampilkan ${shown} dari ${total} data agar halaman tetap ringan.`;
            element.style.display = 'block';
            return;
        }

        element.style.display = 'none';
    }

    function returnToUpload() {
        currentPreviewData = null;
        previewStep.style.display = 'none';
        processingStep.style.display = 'none';
        uploadStep.style.display = 'block';
        fileInput.value = '';
        fileInfo.style.display = 'none';
        validateBtn.disabled = true;
    }

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
            const validCount = Number(data.preview.total || 0);
            const createdCount = Number(data.preview.created_count ?? data.preview.created.length);
            const updatedCount = Number(data.preview.updated_count ?? data.preview.updated.length);
            const errorCount = Number(data.preview.error_count ?? data.preview.errors.length);

            // Update counts
            document.getElementById('createdCount').textContent = createdCount;
            document.getElementById('updatedCount').textContent = updatedCount;
            document.getElementById('errorCount').textContent = errorCount;
            document.getElementById('previewTotals').textContent =
                'Total baris terisi: ' + data.preview.file_rows
                + ' | Valid unik: ' + data.preview.total
                + ' | NIK duplikat: ' + data.preview.duplicate_count;

            document.getElementById('partialImportNotice').style.display = errorCount > 0 ? 'block' : 'none';
            fixFileBtn.style.display = errorCount > 0 ? 'inline-block' : 'none';
            confirmBtn.disabled = validCount === 0;
            confirmBtn.innerHTML = validCount > 0
                ? `<i class="bi bi-check-circle"></i> Import ${validCount} Baris Valid`
                : '<i class="bi bi-x-circle"></i> Tidak Ada Baris Valid';

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
                const createdItems = data.preview.created.slice(0, previewLimit);
                tbody.innerHTML = createdItems.map(item => `<tr>
                    <td><code>${escapeHtml(item.nik)}</code></td>
                    <td><strong>${escapeHtml(item.nama)}</strong></td>
                    <td>${escapeHtml(item.kode_vendor || '-')}</td>
                    <td>${escapeHtml(item.posisi || '-')}</td>
                    <td>${escapeHtml(item.type_lokasi || '-')}</td>
                    <td>${escapeHtml(item.penempatan || '-')}</td>
                    <td>${escapeHtml(item.area_kerja || '-')}</td>
                    <td>${escapeHtml(item.nama_bank || '-')}</td>
                    <td>${escapeHtml(item.no_rekening || '-')}</td>
                    <td>${escapeHtml(item.nama_di_rekening || '-')}</td>
                    <td><span class="badge bg-success">${escapeHtml(item.status)}</span></td>
                    <td>${escapeHtml(item.note1 || '-')}</td>
                </tr>`).join('');
                setPreviewLimitMessage('createdPreviewLimit', createdItems.length, createdCount);
            } else {
                document.getElementById('createdSection').style.display = 'none';
            }

            // Populate updated table
            if (data.preview.updated.length > 0) {
                document.getElementById('updatedSection').style.display = 'block';
                const updatedDiv = document.getElementById('updatedTable');
                const updatedItems = data.preview.updated.slice(0, previewLimit);
                updatedDiv.innerHTML = updatedItems.map(item => {
                    let changesHtml = '<ul style="list-style: none; padding: 0; margin: 0;">';
                    for (const [field, change] of Object.entries(item.changes)) {
                        changesHtml += `<li style="margin: 4px 0; font-size: 0.9rem;">
                            <strong>${escapeHtml(field)}:</strong><br>
                            <code style="color: #dc3545;">${escapeHtml(change.old)}</code>
                            <i class="bi bi-arrow-right"></i> 
                            <code style="color: #198754;">${escapeHtml(change.new)}</code>
                        </li>`;
                    }
                    changesHtml += '</ul>';

                    return `<div class="card mb-2">
                        <div class="card-body p-2">
                            <div style="margin-bottom: 8px;">
                                <strong><code>${escapeHtml(item.nik)}</code> - ${escapeHtml(item.nama)}</strong>
                            </div>
                            ${changesHtml}
                        </div>
                    </div>`;
                }).join('');
                setPreviewLimitMessage('updatedPreviewLimit', updatedItems.length, updatedCount);
            } else {
                document.getElementById('updatedSection').style.display = 'none';
            }

            // Populate errors
            if (data.preview.errors.length > 0) {
                document.getElementById('errorSection').style.display = 'block';
                const errorTable = document.getElementById('errorTable');
                const errorItems = data.preview.errors.slice(0, previewLimit);
                errorTable.innerHTML = errorItems
                    .map(error => `<li>${escapeHtml(error)}</li>`)
                    .join('');
                setPreviewLimitMessage('errorPreviewLimit', errorItems.length, errorCount);
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
        if (!currentPreviewData || Number(currentPreviewData.total || 0) === 0) {
            return;
        }

        const errorCount = Number(currentPreviewData.error_count ?? currentPreviewData.errors.length);
        if (errorCount > 0) {
            const message = errorCount + ' baris error akan dilewati.\n'
                + currentPreviewData.total + ' baris valid akan tetap diimpor.\n\n'
                + 'Lanjutkan import parsial?';
            if (!confirm(message)) {
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
                let resultMessage = data.message
                    + '\nData baru: ' + data.created_count
                    + '\nData diperbarui: ' + data.updated_count
                    + '\nNIK duplikat: ' + data.duplicate_count
                    + '\nError: ' + data.error_count;

                if (data.error_samples && data.error_samples.length > 0) {
                    resultMessage += '\n\nContoh error:\n- ' + data.error_samples.join('\n- ');
                    if (data.error_count > data.error_samples.length) {
                        resultMessage += '\n- ... dan '
                            + (data.error_count - data.error_samples.length)
                            + ' error lainnya';
                    }
                }

                alert(resultMessage);
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

    fixFileBtn.addEventListener('click', returnToUpload);

    // Cancel button
    cancelBtn.addEventListener('click', returnToUpload);
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
