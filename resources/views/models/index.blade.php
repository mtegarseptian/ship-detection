@extends('layouts.app')
@section('title', 'Model AI')
@section('page-title', 'Model AI')
@section('page-subtitle', 'Kelola model AI untuk inferensi deteksi kapal')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('models.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-cloud-upload me-2"></i>Upload Model Baru
    </a>
</div>

<div class="card">
    <div class="card-header p-3">
        <i class="bi bi-cpu me-2 text-primary"></i>Daftar Model AI
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nama Model</th>
                        <th>Versi</th>
                        <th>Format</th>
                        <th>Diunggah Oleh</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($models as $model)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-600" style="font-size:.875rem;">{{ $model->name }}</div>
                            @if($model->description)
                            <div class="text-muted" style="font-size:.75rem;">{{ Str::limit($model->description, 50) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark rounded-pill">{{ $model->version ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary rounded-pill">.{{ strtoupper($model->file_type) }}</span>
                        </td>
                        <td style="font-size:.875rem;">{{ $model->uploader->name ?? '-' }}</td>
                        <td style="font-size:.8rem; color:#6c757d;">{{ $model->created_at->format('d M Y') }}</td>
                        <td>
                            <span class="badge badge-{{ $model->status }} rounded-pill px-3">
                                {{ $model->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <form action="{{ route('models.toggle', $model) }}" method="POST" class="mb-0">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="btn btn-sm {{ $model->status === 'active' ? 'btn-warning' : 'btn-success' }} rounded-2"
                                        title="{{ $model->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="bi bi-{{ $model->status === 'active' ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                </form>
                                <form action="{{ route('models.destroy', $model) }}" method="POST" class="mb-0"
                                      onsubmit="return confirm('Hapus model ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger rounded-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-cpu d-block mb-2" style="font-size:2.5rem;"></i>
                            Belum ada model AI. <a href="{{ route('models.create') }}">Upload sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($models->hasPages())
    <div class="card-footer bg-white">
        {{ $models->links() }}
    </div>
    @endif
</div>

@endsection