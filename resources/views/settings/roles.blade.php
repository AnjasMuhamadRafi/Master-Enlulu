@extends('layouts.app')

@section('title', 'Kelola Role')
@section('page-title', 'Kelola Role & Izin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Role</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Info:</strong> Saat ini sistem memiliki 3 role yang telah ditentukan. Anda dapat mengelola izin untuk setiap role.
                </div>
                
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Role</th>
                            <th>Deskripsi</th>
                            <th>Jumlah User</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-danger">Super Admin</span></td>
                            <td>Akses penuh ke semua fitur sistem</td>
                            <td>1</td>
                            <td><button class="btn btn-sm btn-info">Kelola Izin</button></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-warning">Admin/PIC</span></td>
                            <td>Akses ke fitur manajemen karyawan dan kontrak</td>
                            <td>{{ auth()->user()->role === 'Admin' ? '1' : '0' }}</td>
                            <td><button class="btn btn-sm btn-info">Kelola Izin</button></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-info">Staff</span></td>
                            <td>Akses terbatas - hanya bisa melihat data</td>
                            <td>0</td>
                            <td><button class="btn btn-sm btn-info">Kelola Izin</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
