@extends('layouts.app')

@section('title', 'Activity Log')
@section('page-title', 'Activity Log Sistem')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #fff5f0 0%, #fff9f7 100%);
        padding: 24px 0;
        margin-bottom: 24px;
        border-left: 4px solid #FF6B35;
        padding-left: 24px;
    }
    
    .table-responsive {
        border-radius: 0.375rem;
    }
    
    @media (max-width: 576px) {
        .page-header {
            padding: 16px 0;
            padding-left: 16px;
            margin-bottom: 16px;
        }
        
        .page-header h1 {
            font-size: 1.5rem !important;
        }
        
        .table {
            font-size: 0.85rem;
        }
        
        .table th {
            padding: 0.5rem !important;
        }
        
        .table td {
            padding: 0.5rem !important;
        }
    }
    
    @media (max-width: 768px) {
        .form-label {
            font-size: 0.9rem;
        }
    }
</style>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 text-dark"><i class="bi bi-clock-history" style="color: #FF6B35; margin-right: 8px;"></i> Activity Log Sistem</h1>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <input type="text" name="search" class="form-control" placeholder="Cari deskripsi..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <select name="action" class="form-control">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <select name="user_id" class="form-control">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->user_id }}" {{ request('user_id') == $user->user_id ? 'selected' : '' }}>
                            {{ $user->user->name ?? 'Unknown' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="Dari tanggal">
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="Sampai tanggal">
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>
    </div>
</div>

@if($logs->count() > 0)
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center g-2">
                <div class="col-12 col-lg-auto">
                    <small class="text-muted">Menampilkan <strong>{{ ($logs->currentPage() - 1) * $logs->perPage() + 1 }}</strong> sampai <strong>{{ min($logs->currentPage() * $logs->perPage(), $logs->total()) }}</strong> dari <strong>{{ $logs->total() }}</strong> total log</small>
                </div>
                <div class="col-12 col-lg-auto ms-lg-auto">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <label class="form-label mb-0 text-nowrap">Data per halaman:</label>
                        <select class="form-select form-select-sm d-inline w-auto" id="perPageSelect">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
                <script>
                    document.getElementById('perPageSelect').addEventListener('change', function() {
                        const params = new URLSearchParams(window.location.search);
                        params.set('per_page', this.value);
                        window.location.href = window.location.pathname + '?' + params.toString();
                    });
                </script>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Waktu</th>
                        <th style="min-width: 140px;">User</th>
                        <th style="width: 80px;">Aksi</th>
                        <th style="min-width: 180px;">Model</th>
                        <th style="min-width: 200px;">Keterangan</th>
                        <th style="width: 100px;">IP Address</th>
                        <th style="width: 80px;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>
                                <small class="text-muted">
                                    {{ $log->created_at->format('d/m/Y') }}<br>
                                    <strong>{{ $log->created_at->format('H:i:s') }}</strong>
                                </small>
                            </td>
                            <td>
                                <strong>{{ $log->user->name ?? 'System' }}</strong>
                                <br>
                                <small class="text-muted">{{ $log->user->role ?? '-' }}</small>
                            </td>
                            <td>
                                @php
                                    $actionBadgeClass = match($log->action) {
                                        'create' => 'bg-success',
                                        'update' => 'bg-info',
                                        'delete' => 'bg-danger',
                                        'login' => 'bg-primary',
                                        'logout' => 'bg-secondary',
                                        'export' => 'bg-warning',
                                        'import' => 'bg-warning',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $actionBadgeClass }}">{{ ucfirst($log->action) }}</span>
                            </td>
                            <td>
                                @if($log->model_type)
                                    <small>{{ $log->model_type }}</small><br>
                                    <small class="text-muted">{{ Str::limit($log->model_id, 30) ?? '-' }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <small>{{ Str::limit($log->description, 50) ?? '-' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $log->ip_address ?? '-' }}</small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#detailModal{{ $log->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Detail Modal -->
                        <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Activity Log #{{ $log->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <p style="color: #999; font-size: 12px; margin-bottom: 5px;"><strong>USER</strong></p>
                                                <p>{{ $log->user->name ?? 'System' }} <span class="badge bg-light text-dark">{{ $log->user->role ?? '-' }}</span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p style="color: #999; font-size: 12px; margin-bottom: 5px;"><strong>WAKTU</strong></p>
                                                <p>{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <p style="color: #999; font-size: 12px; margin-bottom: 5px;"><strong>AKSI</strong></p>
                                                <p>
                                                    <span class="badge {{ $actionBadgeClass }}">{{ ucfirst($log->action) }}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p style="color: #999; font-size: 12px; margin-bottom: 5px;"><strong>MODEL</strong></p>
                                                <p>{{ $log->model_type ?? '-' }} ({{ $log->model_id ?? '-' }})</p>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <p style="color: #999; font-size: 12px; margin-bottom: 5px;"><strong>KETERANGAN</strong></p>
                                                <p>{{ $log->description ?? '-' }}</p>
                                            </div>
                                        </div>

                                        {{-- Special handling for Import batch --}}
                                        @if($log->action === 'import' && $log->new_values)
                                            {{-- Summary Stats --}}
                                            @php
                                                $importData = $log->new_values;
                                                $createdCount = is_array($importData['created'] ?? null) ? count($importData['created']) : 0;
                                                $updatedCount = is_array($importData['updated'] ?? null) ? count($importData['updated']) : 0;
                                                $errorCount = is_array($importData['errors'] ?? null) ? count($importData['errors']) : 0;
                                            @endphp
                                            <div class="row mb-3">
                                                <div class="col-4">
                                                    <div style="background: #d4edda; padding: 10px; border-radius: 5px; text-align: center;">
                                                        <p style="color: #999; font-size: 11px; margin: 0;"><strong>DATA DITAMBAH</strong></p>
                                                        <p style="font-size: 18px; font-weight: bold; margin: 5px 0 0 0; color: #155724;">{{ $createdCount }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div style="background: #cfe2ff; padding: 10px; border-radius: 5px; text-align: center;">
                                                        <p style="color: #999; font-size: 11px; margin: 0;"><strong>DATA DIUPDATE</strong></p>
                                                        <p style="font-size: 18px; font-weight: bold; margin: 5px 0 0 0; color: #004085;">{{ $updatedCount }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div style="background: #f8d7da; padding: 10px; border-radius: 5px; text-align: center;">
                                                        <p style="color: #999; font-size: 11px; margin: 0;"><strong>ERROR</strong></p>
                                                        <p style="font-size: 18px; font-weight: bold; margin: 5px 0 0 0; color: #721c24;">{{ $errorCount }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Data Ditambah --}}
                                            @if($createdCount > 0)
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <p style="color: #155724; font-size: 12px; margin-bottom: 8px;"><strong>✓ DATA DITAMBAH ({{ $createdCount }})</strong></p>
                                                    <div style="background: #f1f3f5; padding: 10px; border-radius: 5px; font-size: 0.85rem;">
                                                        @foreach($importData['created'] ?? [] as $item)
                                                            <div style="padding: 6px 0; border-bottom: 1px solid #dee2e6;">
                                                                <strong>{{ $item['nik'] ?? '-' }}</strong> - {{ $item['nama'] ?? '-' }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            {{-- Data Diupdate --}}
                                            @if($updatedCount > 0)
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <p style="color: #004085; font-size: 12px; margin-bottom: 8px;"><strong>↻ DATA DIUPDATE ({{ $updatedCount }})</strong></p>
                                                    <div style="background: #f1f3f5; padding: 10px; border-radius: 5px; font-size: 0.85rem;">
                                                        @foreach($importData['updated'] ?? [] as $item)
                                                            <div style="padding: 8px; margin-bottom: 8px; background: white; border-left: 3px solid #0d6efd; border-radius: 3px;">
                                                                <strong>{{ $item['nik'] ?? '-' }}</strong> - {{ $item['nama'] ?? '-' }}<br>
                                                                @if(!empty($item['changes']))
                                                                    <ul style="margin: 6px 0 0 0; padding-left: 20px; font-size: 0.8rem;">
                                                                        @foreach($item['changes'] as $field => $change)
                                                                            <li>
                                                                                <strong>{{ str_replace('_', ' ', ucfirst($field)) }}:</strong><br>
                                                                                <code style="color: #dc3545; font-size: 0.75rem;">{{ $change['old'] ?? '-' }}</code> 
                                                                                <i class="bi bi-arrow-right" style="color: #999;"></i> 
                                                                                <code style="color: #198754; font-size: 0.75rem;">{{ $change['new'] ?? '-' }}</code>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            {{-- Errors --}}
                                            @if($errorCount > 0)
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <p style="color: #721c24; font-size: 12px; margin-bottom: 8px;"><strong>⚠ ERROR ({{ $errorCount }})</strong></p>
                                                    <div style="background: #f8d7da; padding: 10px; border-radius: 5px; font-size: 0.85rem;">
                                                        @foreach($importData['errors'] ?? [] as $error)
                                                            <div style="padding: 6px 0; border-bottom: 1px solid #f5c6cb;">
                                                                {{ $error }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                        @else
                                            {{-- Standard log display for non-import actions --}}
                                            @if($log->old_values)
                                                <div class="row mb-3">
                                                    <div class="col-12">
                                                        <p style="color: #999; font-size: 12px; margin-bottom: 5px;"><strong>NILAI LAMA</strong></p>
                                                        <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; font-size: 11px; max-height: 200px; overflow-y: auto; margin: 0;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($log->new_values)
                                                <div class="row mb-3">
                                                    <div class="col-12">
                                                        <p style="color: #999; font-size: 12px; margin-bottom: 5px;"><strong>NILAI BARU</strong></p>
                                                        <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; font-size: 11px; max-height: 200px; overflow-y: auto; margin: 0;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        <div class="row">
                                            <div class="col-12">
                                                <p style="color: #999; font-size: 12px; margin-bottom: 5px;"><strong>INFORMASI TEKNIS</strong></p>
                                                <p><small><strong>IP Address:</strong> {{ $log->ip_address ?? '-' }}</small></p>
                                                <p><small><strong>User Agent:</strong><br>{{ Str::limit($log->user_agent, 100) ?? '-' }}</small></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($logs->hasPages())
    <div class="d-flex justify-content-center mt-4 mb-4">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0 flex-wrap">
                {{-- Previous Page Link --}}
                @if ($logs->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">← Previous</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $logs->previousPageUrl() }}&search={{ request('search') }}&action={{ request('action') }}&user_id={{ request('user_id') }}&date_from={{ request('date_from') }}&date_to={{ request('date_to') }}">← Previous</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                    @if ($page == $logs->currentPage())
                        <li class="page-item active">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}&search={{ request('search') }}&action={{ request('action') }}&user_id={{ request('user_id') }}&date_from={{ request('date_from') }}&date_to={{ request('date_to') }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($logs->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $logs->nextPageUrl() }}&search={{ request('search') }}&action={{ request('action') }}&user_id={{ request('user_id') }}&date_from={{ request('date_from') }}&date_to={{ request('date_to') }}">Next →</a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">Next →</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
@else
    <div class="alert alert-info" role="alert">
        <i class="bi bi-info-circle"></i> Tidak ada activity log yang tersedia. Mulai lakukan aksi untuk mencatat aktivitas.
    </div>
@endif

@endsection