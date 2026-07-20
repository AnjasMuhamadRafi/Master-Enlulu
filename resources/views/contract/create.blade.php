@extends('layouts.app')

@section('title', 'Buat Kontrak')
@section('page-title', 'Buat Kontrak Baru')

@section('content')
<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Generate Perjanjian Kerja Kemitraan</h5>
            </div>
            <div class="card-body">
                @if($employees->isEmpty())
                    <div class="alert alert-warning mb-0">
                        Belum ada karyawan yang dapat dipilih. Pastikan data karyawan dan akses posisi sudah tersedia.
                    </div>
                @else
                <form method="POST" action="{{ route('contract.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Karyawan *</label>
                        <input
                            type="search"
                            class="form-control mb-2"
                            id="employeeSearch"
                            placeholder="Cari nama, NIK KTP, atau NIK..."
                            autocomplete="off">
                        <select class="form-select @error('employee_nik') is-invalid @enderror" name="employee_nik" id="employeeSelect" required>
                            <option value="" data-search="">Pilih Karyawan</option>
                            @foreach($employees as $employee)
                                <option
                                    value="{{ $employee->nik }}"
                                    data-number="{{ $employee->no_pks_masuk }}"
                                    data-client="{{ $employee->nama_customer }}"
                                    data-start="{{ optional($employee->tanggal_masuk)->format('Y-m-d') }}"
                                    data-end="{{ optional($employee->tanggal_keluar)->format('Y-m-d') }}"
                                    data-name="{{ strtolower($employee->nama_lengkap) }}"
                                    data-ktp="{{ strtolower($employee->nik_ktp ?? '') }}"
                                    data-nik="{{ strtolower($employee->nik) }}"
                                    data-search="{{ strtolower($employee->nama_lengkap.' '.$employee->nik.' '.($employee->nik_ktp ?? '')) }}"
                                    {{ old('employee_nik') === $employee->nik ? 'selected' : '' }}>
                                    {{ $employee->nama_lengkap }} | NIK KTP: {{ $employee->nik_ktp ?: '-' }} | NIK: {{ $employee->nik }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="employeeSearchInfo">Menampilkan {{ $employees->count() }} karyawan</small>
                        @error('employee_nik')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. PKS</label>
                            <input type="text" class="form-control @error('contract_number') is-invalid @enderror" name="contract_number" id="contractNumber" value="{{ old('contract_number') }}" placeholder="Otomatis dari No. PKS Masuk">
                            @error('contract_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer / Klien</label>
                            <input type="text" class="form-control" id="clientName" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Dibuat tanggal *</label>
                            <input type="date" class="form-control @error('contract_date') is-invalid @enderror" name="contract_date" id="contractDate" value="{{ old('contract_date') }}" required>
                            @error('contract_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal mulai *</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" id="startDate" value="{{ old('start_date') }}" required>
                            @error('start_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal berakhir</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" id="endDate" value="{{ old('end_date') }}">
                            @error('end_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="alert alert-light border">
                        <i class="bi bi-info-circle me-1"></i>
                        Tanggal pada dokumen akan ditulis dengan nama bulan Indonesia, contoh: <strong>25 Mei 2026</strong>.
                        Data identitas, posisi, NIK ENLULU, dan klien diambil dari master karyawan.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-file-earmark-word me-1"></i> Simpan Kontrak</button>
                        <a href="{{ route('contract.index') }}" class="btn btn-secondary"><i class="bi bi-x me-1"></i> Batal</a>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!$employees->isEmpty())
<script>
    const employeeSelect = document.getElementById('employeeSelect');
    const employeeSearch = document.getElementById('employeeSearch');
    const employeeSearchInfo = document.getElementById('employeeSearchInfo');
    const contractNumber = document.getElementById('contractNumber');
    const clientName = document.getElementById('clientName');
    const contractDate = document.getElementById('contractDate');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');

    employeeSearch.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        let visible = 0;
        let exactMatch = null;

        Array.from(employeeSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            const matches = !query || option.dataset.search.includes(query);
            option.hidden = !matches;
            if (matches) {
                visible++;
                if ([option.dataset.name, option.dataset.ktp, option.dataset.nik].includes(query)) {
                    exactMatch = option;
                }
            }
        });

        employeeSearchInfo.textContent = `Menampilkan ${visible} karyawan`;

        if (exactMatch) {
            employeeSelect.value = exactMatch.value;
            employeeSelect.dispatchEvent(new Event('change'));
        } else if (employeeSelect.selectedOptions[0]?.hidden) {
            employeeSelect.value = '';
        }
    });

    employeeSelect.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        if (!option.value) return;
        contractNumber.value = option.dataset.number || '';
        clientName.value = option.dataset.client || '-';
        contractDate.value = option.dataset.start || '';
        startDate.value = option.dataset.start || '';
        endDate.value = option.dataset.end || '';
    });
    employeeSelect.dispatchEvent(new Event('change'));
</script>
@endif
@endsection
