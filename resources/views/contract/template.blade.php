@extends('layouts.app')

@section('title', 'Template Kontrak')
@section('page-title', 'Template Kontrak')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Template Kontrak Aktif</h5></div>
    <div class="card-body">
        <div class="alert alert-info mb-0">
            <strong>{{ \App\Services\ContractDocumentService::TEMPLATE_NAME }}</strong>
            digunakan untuk membuat Perjanjian Kerja Kemitraan. Field otomatis mencakup No PKS, tanggal Indonesia,
            identitas karyawan, NIK ENLULU, posisi, dan nama customer/klien.
        </div>
    </div>
</div>
@endsection
