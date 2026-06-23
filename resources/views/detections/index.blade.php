@extends('layouts.app')
@section('title', 'Riwayat Deteksi')
@section('page-title', 'Riwayat Deteksi')
@section('page-subtitle', 'Semua proses deteksi kapal yang telah dilakukan')

@section('content')

{{-- Bagian Atas: Filter & Tombol Aksi --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    
    {{-- Fitur Filter Khusus Admin --}}
    <div class="w-100" style="max-width: 400px;">
        @if(auth()->user()->isAdmin() && isset($operators) && count($operators) > 0)
        <form action="{{ route('detections.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <i class="bi bi-funnel text-muted" style="font-size: 1.2rem;"></i>
            <select name="operator_id" class="form-select border-0 shadow-sm" onchange="this.form.submit()">
                <option value="">-- Semua Operator --</option>
                @foreach($operators as $op)
                    <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                        {{ $op->name }} ({{ $op->detections_count }} Deteksi)
                    </option>
                @endforeach
            </select>
            @if(request('operator_id'))
                <a href="{{ route('detections.index') }}" class="btn btn-light text-danger shadow-sm border-0" title="Hapus Filter">
                    <i class="bi bi-x-circle-fill"></i>
                </a>
            @endif
        </form>
        @endif
    </div>

    {{-- Tombol Aksi Kanan --}}
    <div class="d-flex gap-2">
        {{-- Tombol Hapus Semua (Hanya aktif jika ada data) --}}
        @if($detections->total() > 0)
        <form action="{{ route('detections.clear') }}" method="POST" class="mb-0" onsubmit="return confirm('Peringatan: Yakin ingin menghapus SEMUA riwayat ini beserta gambarnya? Tindakan ini tidak bisa dibatalkan.');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger shadow-sm border-0 bg-white">
                <i class="bi bi-trash2 me-2"></i>Hapus Semua
            </button>
        </form>
        @endif

        <a href="{{ route('detections.create') }}" class="btn btn-primary rounded-3 shadow-sm">
            <i class="bi bi-plus-circle me-2"></i>Deteksi Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header p-3 bg-white border-bottom">
        <i class="bi bi-clock-history me-2 text-primary"></i>Semua Deteksi
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:60px;">#</th>
                        <th>Citra</th>
                        <th>Model AI</th>
                        @if(auth()->user()->isAdmin())<th>Operator</th>@endif
                        <th>Kapal</th>
                        <th>Status</th>
                        <th>Waktu</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detections as $d)
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:.8rem;">{{ $d->id }}</td>
                        <td>
                            <div style="width:52px; height:40px; border-radius:6px; overflow:hidden; background:#e9ecef;">
                                <img src="{{ Storage::url($d->image_original) }}"
                                     style="width:100%;height:100%;object-fit:cover;"
                                     onerror="this.style.display='none'">
                            </div>
                        </td>
                        <td style="font-size:.8rem;">{{ $d->modelAI->name ?? '<span class=\'text-muted\'>—</span>' }}</td>
                        @if(auth()->user()->isAdmin())
                        <td style="font-size:.8rem;">{{ $d->user->name ?? '-' }}</td>
                        @endif
                        <td>
                            <span class="fw-600">{{ $d->ship_count }}</span>
                            <span class="text-muted" style="font-size:.75rem;"> kapal</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $d->status }} rounded-pill px-3" style="font-size:.7rem;">
                                @switch($d->status)
                                    @case('pending') <i class="bi bi-hourglass me-1"></i>Menunggu @break
                                    @case('processing') <i class="bi bi-arrow-repeat me-1"></i>Proses @break
                                    @case('done') <i class="bi bi-check me-1"></i>Selesai @break
                                    @case('failed') <i class="bi bi-x me-1"></i>Gagal @break
                                @endswitch
                            </span>
                        </td>
                        <td style="font-size:.75rem; color:#6c757d;">{{ $d->created_at->format('d M Y H:i') }}</td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('detections.show', $d) }}"
                                   class="btn btn-sm btn-outline-primary rounded-2" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                @if(auth()->id() === $d->user_id || auth()->user()->isAdmin())
                                <form action="{{ route('detections.destroy', $d) }}" method="POST" class="mb-0" onsubmit="return confirm('Hapus riwayat deteksi ini beserta gambarnya secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-2" title="Hapus Riwayat">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:2.5rem;"></i>
                            Belum ada riwayat deteksi.
                            <a href="{{ route('detections.create') }}">Mulai deteksi sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($detections->hasPages())
    <div class="card-footer bg-white border-top-0">{{ $detections->links() }}</div>
    @endif
</div>

@endsection