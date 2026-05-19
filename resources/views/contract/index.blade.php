@extends('layouts.app')

@section('title', 'Daftar Kontrak')
@section('page-title', 'Daftar Kontrak')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h4>Manajemen Kontrak</h4>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('contract.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Buat Kontrak Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nomor Kontrak</th>
                    <th>Nama Karyawan</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Berakhir</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada data kontrak. <a href="{{ route('contract.create') }}">Buat kontrak baru</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
