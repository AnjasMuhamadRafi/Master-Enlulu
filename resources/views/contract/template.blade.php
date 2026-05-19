@extends('layouts.app')

@section('title', 'Template Kontrak')
@section('page-title', 'Template Kontrak')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Template Kontrak</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Info:</strong> Template kontrak akan disediakan di sini. Anda dapat menggunakan template ini sebagai dasar pembuatan kontrak baru.
                </div>
                
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Template</th>
                            <th>Tipe Kontrak</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Belum ada template kontrak. Template akan diunggah oleh Super Admin.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
