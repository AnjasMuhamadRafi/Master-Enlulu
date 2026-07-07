@extends('layouts.app')

@section('title', 'Kelola User')
@section('page-title', 'Kelola User Sistem')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Gagal menyimpan user:</strong>
    <ul class="mb-0 mt-1 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row mb-4">
    <div class="col-md-6">
        <h4>Daftar User</h4>
    </div>
    <div class="col-md-6 text-end">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
            <i class="bi bi-plus-circle"></i> Tambah User Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Posisi Dikelola</th>
                    <th>Status</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users ?? [] as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge bg-{{ $user->role === 'Super Admin' ? 'danger' : ($user->role === 'Admin' ? 'warning' : 'info') }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td>
                        @php $positions = $user->getManagedPositions(); @endphp
                        @if(!empty($positions))
                            @foreach($positions as $pos)
                                <span class="badge bg-light text-dark me-1">{{ $pos }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td><span class="badge bg-success">Aktif</span></td>
                    <td>{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('settings.users.destroy', $user->id) }}" style="display:inline;" onsubmit="return confirm('Yakin hapus user {{ $user->name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach

                @if(empty($users) || $users->count() === 0)
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Belum ada user terdaftar.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="tambahUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.users.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                        @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                        @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select @error('role') is-invalid @enderror role-select" data-form="create" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="Super Admin" {{ old('role') === 'Super Admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="Admin" {{ old('role') === 'Admin' ? 'selected' : '' }}>Admin/PIC</option>
                            <option value="Staff" {{ old('role') === 'Staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                        @error('role')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-3 position-field-create" @if(old('role') !== 'Admin') style="display: none;" @endif>
                        <label class="form-label">Posisi Dikelola</label>
                        <div class="border rounded p-2">
                            @php
                                $createSelectedPositions = collect(old('handled_position', []))
                                    ->map(fn($position) => trim(strtoupper(preg_replace('/^position:/i', '', (string) $position))))
                                    ->filter()
                                    ->values()
                                    ->all();
                                $createPositionChoices = collect($availablePositions ?? [])
                                    ->merge($createSelectedPositions)
                                    ->unique()
                                    ->sort()
                                    ->values();
                            @endphp

                            <div class="managed-position-list">
                                @forelse($createPositionChoices as $position)
                                    @php $positionId = md5($position); @endphp
                                    <div class="form-check">
                                        <input class="form-check-input position-checkbox-create" type="checkbox"
                                               name="handled_position[]" value="{{ $position }}"
                                               id="posisiCreate_{{ $positionId }}"
                                               {{ in_array($position, $createSelectedPositions, true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="posisiCreate_{{ $positionId }}">
                                            {{ $position }}
                                        </label>
                                    </div>
                                @empty
                                    <div class="text-muted small">Belum ada posisi di master karyawan.</div>
                                @endforelse
                            </div>

                            <div class="mt-2">
                                <label class="form-label small mb-1">Posisi tambahan</label>
                                <textarea class="form-control form-control-sm" name="handled_position_manual" rows="2" placeholder="Contoh: HELPER SORTIR">{{ old('handled_position_manual') }}</textarea>
                                <div class="form-text">Pisahkan beberapa posisi dengan koma atau baris baru.</div>
                            </div>
                        </div>
                        @error('handled_position')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        @error('handled_position_manual')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                        @error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modals (dinamis per user) -->
@foreach($users ?? [] as $user)
<div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.users.update', $user->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select @error('role') is-invalid @enderror role-select" data-form="edit{{ $user->id }}" data-user-id="{{ $user->id }}" name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="Super Admin" {{ old('role', $user->role) === 'Super Admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="Admin" {{ old('role', $user->role) === 'Admin' ? 'selected' : '' }}>Admin/PIC</option>
                            <option value="Staff" {{ old('role', $user->role) === 'Staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                        @error('role')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>
                    <div class="mb-3 position-field-edit{{ $user->id }}" @if(old('role', $user->role) !== 'Admin') style="display: none;" @endif>
                        <label class="form-label">Posisi Dikelola</label>
                        <div class="border rounded p-2">
                            @php
                                $currentPositions = is_array(old('handled_position'))
                                    ? collect(old('handled_position'))->map(fn($position) => trim(strtoupper(preg_replace('/^position:/i', '', (string) $position))))->filter()->values()->all()
                                    : $user->getManagedPositions();
                                $editPositionChoices = collect($availablePositions ?? [])
                                    ->merge($currentPositions)
                                    ->unique()
                                    ->sort()
                                    ->values();
                            @endphp

                            <div class="managed-position-list">
                                @forelse($editPositionChoices as $position)
                                    @php $positionId = md5($user->id . $position); @endphp
                                    <div class="form-check">
                                        <input class="form-check-input position-checkbox-edit{{ $user->id }}" type="checkbox"
                                               name="handled_position[]" value="{{ $position }}"
                                               id="posisiEdit{{ $user->id }}_{{ $positionId }}"
                                               {{ in_array($position, $currentPositions, true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="posisiEdit{{ $user->id }}_{{ $positionId }}">
                                            {{ $position }}
                                        </label>
                                    </div>
                                @empty
                                    <div class="text-muted small">Belum ada posisi di master karyawan.</div>
                                @endforelse
                            </div>

                            <div class="mt-2">
                                <label class="form-label small mb-1">Posisi tambahan</label>
                                <textarea class="form-control form-control-sm" name="handled_position_manual" rows="2" placeholder="Contoh: HELPER SORTIR">{{ old('handled_position_manual') }}</textarea>
                                <div class="form-text">Pisahkan beberapa posisi dengan koma atau baris baru.</div>
                            </div>
                        </div>
                        @error('handled_position')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                        @error('handled_position_manual')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<style>
    .managed-position-list {
        max-height: 220px;
        overflow-y: auto;
    }
</style>

<script>
    document.querySelectorAll('.role-select').forEach(select => {
        const form = select.dataset.form;
        const userId = select.dataset.userId;

        select.addEventListener('change', function () {
            let container, checkboxes;

            if (form === 'create') {
                container  = document.querySelector('.position-field-create');
                checkboxes = document.querySelectorAll('.position-checkbox-create');
            } else {
                container  = document.querySelector(`.position-field-edit${userId}`);
                checkboxes = document.querySelectorAll(`.position-checkbox-edit${userId}`);
            }

            if (this.value === 'Admin') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
                checkboxes.forEach(cb => cb.checked = false);
            }
        });
    });
</script>

@endsection
