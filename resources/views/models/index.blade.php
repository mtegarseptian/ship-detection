@extends('layouts.app')
@section('title', 'Model AI')
@section('page-title', 'Model AI')
@section('page-subtitle', 'Kelola model AI dan lihat metrik evaluasi hasil training')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <a href="{{ route('models.create') }}" class="btn btn-primary rounded-3 fw-600 shadow-sm">
        <i class="bi bi-cloud-upload me-2"></i>Upload Paket Model
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white p-3 border-bottom">
        <i class="bi bi-cpu me-2 text-primary"></i>Daftar Model AI
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nama Model</th>
                        <th>Versi</th>
                        <th>mAP50</th>
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
                            <span class="badge bg-light text-dark rounded-pill border">{{ $model->version ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-success" style="font-size:.875rem;">
                                {{ $model->map50 ? round($model->map50 * 100, 1) . '%' : '-' }}
                            </div>
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
                                
                                {{-- Tombol Modal Detail Grafik --}}
                                <button type="button" class="btn btn-sm btn-info text-white rounded-2" data-bs-toggle="modal" data-bs-target="#modalDetail-{{ $model->id }}" title="Lihat Metrik & Grafik">
                                    <i class="bi bi-bar-chart-fill"></i>
                                </button>

                                {{-- Tombol Saklar Keamanan --}}
                                <form action="{{ route('models.toggle', $model) }}" method="POST" class="mb-0">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="btn btn-sm {{ $model->status === 'active' ? 'btn-warning' : 'btn-success' }} rounded-2"
                                        title="{{ $model->status === 'active' ? 'Matikan Sementara' : 'Aktifkan' }}">
                                        <i class="bi bi-{{ $model->status === 'active' ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                </form>

                                {{-- Tombol Hapus --}}
                                <form action="{{ route('models.destroy', $model) }}" method="POST" class="mb-0"
                                      onsubmit="return confirm('Hapus model ini secara permanen?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger rounded-2" title="Hapus Permanen">
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
    <div class="card-footer bg-white border-top-0">
        {{ $models->links() }}
    </div>
    @endif
</div>

{{-- ================= MODAL LOOPING ================= --}}
@foreach($models as $model)
<div class="modal fade" id="modalDetail-{{ $model->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cpu text-primary me-2"></i>{{ $model->name }} 
                    <span class="badge bg-secondary ms-2" style="font-size:0.75rem;">{{ $model->version }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                
                {{-- Bagian Atas: Tabel Informasi & Metrik --}}
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100 bg-light">
                            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-gear-fill me-2"></i>Konfigurasi Training (args.yaml)</h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.9rem;">
                                <tr><td class="text-muted" width="40%">Arsitektur Base</td><td class="fw-semibold">: {{ $model->base_model ?? 'N/A' }}</td></tr>
                                <tr><td class="text-muted">Total Epochs</td><td class="fw-semibold">: {{ $model->epochs ?? 'N/A' }}</td></tr>
                                <tr><td class="text-muted">Batch Size</td><td class="fw-semibold">: {{ $model->batch_size ?? 'N/A' }}</td></tr>
                                <tr><td class="text-muted">Image Size (imgsz)</td><td class="fw-semibold">: {{ $model->imgsz ?? 'N/A' }} px</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100 bg-light">
                            <h6 class="fw-bold mb-3 text-success border-bottom pb-2"><i class="bi bi-check-circle-fill me-2"></i>Akurasi Akhir (results.csv)</h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.9rem;">
                                <tr><td class="text-muted" width="40%">mAP50</td><td class="fw-bold text-success" style="font-size: 1.1rem;">: {{ $model->map50 ? round($model->map50 * 100, 2) . '%' : 'N/A' }}</td></tr>
                                <tr><td class="text-muted">mAP50-95</td><td class="fw-semibold">: {{ $model->map50_95 ? round($model->map50_95 * 100, 2) . '%' : 'N/A' }}</td></tr>
                                <tr><td class="text-muted">Precision</td><td class="fw-semibold">: {{ $model->precision ? round($model->precision * 100, 2) . '%' : 'N/A' }}</td></tr>
                                <tr><td class="text-muted">Recall</td><td class="fw-semibold">: {{ $model->recall ? round($model->recall * 100, 2) . '%' : 'N/A' }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Bagian Bawah: Galeri Grafik --}}
                <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-images me-2"></i>Grafik Evaluasi Model</h6>
                <div class="row g-3">
                    @if(!empty($model->metrics_images) && is_array($model->metrics_images))
                        @foreach($model->metrics_images as $namaGrafik => $path)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border shadow-sm">
                                <div class="card-body p-2 text-center">
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank" title="Klik untuk memperbesar">
                                        <img src="{{ asset('storage/' . $path) }}" class="img-fluid rounded" alt="{{ $namaGrafik }}" style="max-height: 250px; object-fit: contain;">
                                    </a>
                                </div>
                                <div class="card-footer bg-white text-center border-top-0 py-2">
                                    <small class="text-muted fw-600">{{ $namaGrafik }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center py-4 bg-light rounded-3 border">
                            <i class="bi bi-image text-muted d-block mb-2" style="font-size: 2rem;"></i>
                            <span class="text-muted">Grafik evaluasi tidak ditemukan pada paket ini.</span>
                        </div>
                    @endif
                </div>

            </div>
            <div class="modal-footer border-top-0 bg-light">
                <button type="button" class="btn btn-secondary fw-600 rounded-3 px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection