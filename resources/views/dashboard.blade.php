@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan aktivitas sistem deteksi kapal')

@section('content')

{{-- ── STAT CARDS ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <p class="text-muted mb-1" style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Total Deteksi</p>
                    <h3 class="mb-0 fw-700">{{ number_format($totalDetections) }}</h3>
                </div>
                <div class="stat-icon" style="background:#e8f4fd; color:#0d6efd;">
                    <i class="bi bi-search"></i>
                </div>
            </div>
            <small class="text-muted">Seluruh proses inferensi</small>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <p class="text-muted mb-1" style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Selesai</p>
                    <h3 class="mb-0 fw-700">{{ number_format($doneDetections) }}</h3>
                </div>
                <div class="stat-icon" style="background:#d1e7dd; color:#198754;">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
            <small class="text-muted">Deteksi berhasil</small>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <p class="text-muted mb-1" style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Kapal Terdeteksi</p>
                    <h3 class="mb-0 fw-700">{{ number_format($totalShips) }}</h3>
                </div>
                <div class="stat-icon" style="background:#fff3cd; color:#ffc107;">
                    <i class="bi bi-water"></i>
                </div>
            </div>
            <small class="text-muted">Total kapal ditemukan</small>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <p class="text-muted mb-1" style="font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Model Aktif</p>
                    <h3 class="mb-0 fw-700">{{ $totalModels }}</h3>
                </div>
                <div class="stat-icon" style="background:#f3e8ff; color:#7c3aed;">
                    <i class="bi bi-cpu"></i>
                </div>
            </div>
            <small class="text-muted">Model AI siap pakai</small>
        </div>
    </div>
</div>

{{-- ── CHART & RECENT ── --}}
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header p-3 d-flex align-items-center justify-content-between">
                <span><i class="bi bi-bar-chart me-2 text-primary"></i>Aktivitas Deteksi (7 Hari)</span>
            </div>
            <div class="card-body p-3">
                <canvas id="detectionChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header p-3">
                <i class="bi bi-clock-history me-2 text-primary"></i>Deteksi Terbaru
            </div>
            <div class="card-body p-0">
                @forelse($recentDetections as $d)
                <a href="{{ route('detections.show', $d) }}"
                   class="d-flex align-items-center gap-3 p-3 border-bottom text-decoration-none text-dark"
                   style="transition:background .15s" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background=''">
                    <div style="width:42px; height:42px; border-radius:8px; overflow:hidden; flex-shrink:0; background:#e9ecef;">
                        <img src="{{ Storage::url($d->image_original) }}" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'">
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:.8rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ basename($d->image_original) }}
                        </div>
                        <div style="font-size:.7rem; color:#6c757d;">{{ $d->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="badge badge-{{ $d->status }} rounded-pill" style="font-size:.65rem;">
                        {{ ucfirst($d->status) }}
                    </span>
                </a>
                @empty
                <div class="p-4 text-center text-muted" style="font-size:.875rem;">
                    <i class="bi bi-inbox d-block mb-2" style="font-size:2rem;"></i>
                    Belum ada deteksi
                </div>
                @endforelse
            </div>
            <div class="card-footer bg-white text-center p-2">
                <a href="{{ route('detections.index') }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const chartData = @json($chartData);
const labels = chartData.map(d => d.date);
const totals = chartData.map(d => d.total);
const ships  = chartData.map(d => d.ships);

new Chart(document.getElementById('detectionChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Jumlah Deteksi',
                data: totals,
                backgroundColor: 'rgba(13,110,253,.15)',
                borderColor: '#0d6efd',
                borderWidth: 2,
                borderRadius: 6,
                yAxisID: 'y'
            },
            {
                label: 'Kapal Terdeteksi',
                data: ships,
                type: 'line',
                borderColor: '#20c997',
                backgroundColor: 'rgba(32,201,151,.1)',
                borderWidth: 2,
                pointRadius: 4,
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'top' } },
        scales: {
            y:  { beginAtZero: true, grid: { color: '#f0f0f0' }, title: { display: true, text: 'Deteksi' } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Kapal' } }
        }
    }
});
</script>
@endpush