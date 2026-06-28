@extends('layouts.app')
@section('title', 'Model AI')
@section('page-title', 'Model AI')
@section('page-subtitle', 'Kelola model AI dan lihat metrik evaluasi hasil training')

@push('styles')
<style>
    /* Area Scroll Parameter */
    .param-scroll-area { 
        max-height: 280px; 
        overflow-y: auto; 
        padding-right: 10px;
    }
    .param-scroll-area::-webkit-scrollbar { width: 6px; }
    .param-scroll-area::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
    .param-scroll-area::-webkit-scrollbar-track { background-color: transparent; }
    
    /* Area Scroll Gambar */
    .img-scroll-area {
        max-height: 350px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 10px;
    }
    .img-scroll-area::-webkit-scrollbar { width: 6px; }
    .img-scroll-area::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

    /* Dropdown Filter Kustom */
    .dropdown-filter-menu {
        min-width: 280px;
        max-height: 300px;
        overflow-y: auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.5rem 0;
    }
    .dropdown-filter-menu::-webkit-scrollbar { width: 5px; }
    .dropdown-filter-menu::-webkit-scrollbar-thumb { background-color: #94a3b8; border-radius: 10px; }
    
    .dropdown-item-custom {
        padding: 0.35rem 1.25rem;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .dropdown-item-custom:hover { background-color: #f1f5f9; }
    .form-check-input { cursor: pointer; margin-top: 0; }
    .form-check-label { cursor: pointer; font-size: 0.85rem; user-select: none; width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Items YAML */
    .param-item { border-bottom: 1px dashed #e2e8f0; padding: 10px 0; display: flex; align-items: start; justify-content: space-between; transition: all 0.2s; }
    .param-item:last-child { border-bottom: none; }
    .param-name { font-weight: 600; color: #1e293b; font-size: 0.85rem; font-family: monospace; }
    .param-val { background: #e0e7ff; color: #4338ca; padding: 2px 10px; border-radius: 6px; font-size: 0.8rem; font-family: monospace; font-weight: bold; max-width: 60%; word-break: break-all; text-align: right;}
    
    .img-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
    .img-item { transition: all 0.3s ease; }
</style>
@endpush

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
                                {{-- Tombol Modal Detail (Tanpa Teks) --}}
                                <button type="button" class="btn btn-sm btn-info text-white rounded-2" data-bs-toggle="modal" data-bs-target="#modalDetail-{{ $model->id }}" title="Lihat Metrik & Grafik">
                                    <i class="bi bi-bar-chart-fill"></i>
                                </button>

                                <form action="{{ route('models.toggle', $model) }}" method="POST" class="mb-0">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $model->status === 'active' ? 'btn-warning' : 'btn-success' }} rounded-2" title="{{ $model->status === 'active' ? 'Matikan Sementara' : 'Aktifkan' }}">
                                        <i class="bi bi-{{ $model->status === 'active' ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                </form>
                                <form action="{{ route('models.destroy', $model) }}" method="POST" class="mb-0" onsubmit="return confirm('Hapus model ini secara permanen?')">
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
    <div class="card-footer bg-white border-top-0">{{ $models->links() }}</div>
    @endif
</div>

{{-- ================= ARRAY URUTAN ORIGINAL YAML ================= --}}
@php
    $orderedKeys = [
        'task', 'mode', 'model', 'data', 'epochs', 'time', 'patience', 'batch', 'imgsz',
        'save', 'save_period', 'cache', 'device', 'workers', 'project', 'name', 'exist_ok',
        'pretrained', 'optimizer', 'verbose', 'seed', 'deterministic', 'single_cls', 'rect',
        'cos_lr', 'close_mosaic', 'resume', 'amp', 'fraction', 'profile', 'freeze',
        'multi_scale', 'compile', 'overlap_mask', 'mask_ratio', 'dropout', 'val', 'split',
        'save_json', 'conf', 'iou', 'max_det', 'half', 'dnn', 'plots', 'source', 'vid_stride',
        'stream_buffer', 'visualize', 'augment', 'agnostic_nms', 'classes', 'retina_masks',
        'embed', 'show', 'save_frames', 'save_txt', 'save_conf', 'save_crop', 'show_labels',
        'show_conf', 'show_boxes', 'line_width', 'format', 'keras', 'optimize', 'int8',
        'dynamic', 'simplify', 'opset', 'workspace', 'nms', 'lr0', 'lrf', 'momentum',
        'weight_decay', 'warmup_epochs', 'warmup_momentum', 'warmup_bias_lr', 'box', 'cls',
        'dfl', 'pose', 'kobj', 'rle', 'angle', 'nbs', 'hsv_h', 'hsv_s', 'hsv_v', 'degrees',
        'translate', 'scale', 'shear', 'perspective', 'flipud', 'fliplr', 'bgr', 'mosaic',
        'mixup', 'cutmix', 'copy_paste', 'copy_paste_mode', 'auto_augment', 'erasing',
        'cfg', 'tracker', 'save_dir'
    ];
@endphp

{{-- ================= MODAL LOOPING DETAIL ================= --}}
@foreach($models as $model)

@php
    $rawYamlData = is_array($model->args_yaml) ? $model->args_yaml : [];
    $yamlData = [];
    
    // 1. Susun data berdasarkan array urutan original di atas
    foreach($orderedKeys as $key) {
        if(array_key_exists($key, $rawYamlData)) {
            $yamlData[$key] = $rawYamlData[$key];
            unset($rawYamlData[$key]); // Hapus agar tidak duplikat
        }
    }
    // 2. Tambahkan sisa data (jika ada parameter baru dari YOLO di luar list di atas)
    foreach($rawYamlData as $key => $val) {
        $yamlData[$key] = $val;
    }

    $grafik = is_array($model->metrics_images) ? $model->metrics_images : [];
    $batch  = is_array($model->batch_images) ? $model->batch_images : [];
    $allImages = array_merge($grafik, $batch);
@endphp

<div class="modal fade" id="modalDetail-{{ $model->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            
            <div class="modal-header bg-light border-bottom p-3">
                <h5 class="modal-title fw-bold mb-0">
                    <i class="bi bi-cpu text-primary me-2"></i>{{ $model->name }}
                    <span class="badge bg-secondary ms-1">{{ $model->version }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-white">
                
                {{-- Bagian Atas: 2 Kolom --}}
                <div class="row g-4 mb-4">
                    
                    {{-- KOLOM KIRI: KONFIGURASI ARGS.YAML --}}
                    <div class="col-md-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-2">
                                <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-file-earmark-code me-2"></i>Konfigurasi (args.yaml)</h6>
                                
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                        <i class="bi bi-funnel-fill me-1"></i> Filter
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-filter-menu shadow">
                                        @foreach($yamlData as $key => $val)
                                            @php $safeKey = Str::slug($key); @endphp
                                            <li class="dropdown-item-custom">
                                                <input class="form-check-input filter-checkbox" type="checkbox" id="cb-yaml-{{$model->id}}-{{$safeKey}}" data-target=".item-yaml-{{$model->id}}-{{$safeKey}}" checked>
                                                <label class="form-check-label" for="cb-yaml-{{$model->id}}-{{$safeKey}}" title="{{ $key }}">{{ $key }}</label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="card-body param-scroll-area">
                                @forelse($yamlData as $key => $val)
                                    @php 
                                        $safeKey = Str::slug($key);
                                        $valStr = is_array($val) ? json_encode($val) : (is_bool($val) ? ($val ? 'true' : 'false') : (string)$val); 
                                    @endphp
                                    <div class="param-item item-yaml-{{$model->id}}-{{$safeKey}}">
                                        <span class="param-name">{{ $key }}</span>
                                        <span class="param-val">{{ $valStr === '' ? 'null' : $valStr }}</span>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3"><i class="bi bi-info-circle me-1"></i> Data args.yaml tidak tersedia.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- KOLOM KANAN: AKURASI AKHIR (CSV) --}}
                    <div class="col-md-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-header bg-light border-bottom py-3">
                                <h6 class="fw-bold mb-0 text-success"><i class="bi bi-check-circle-fill me-2"></i>Akurasi Akhir (results.csv)</h6>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center px-4">
                                
                                {{-- PERBAIKAN: Kotak mAP dan Precision dibuat presisi 50:50 dengan Flexbox --}}
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <div class="p-3 border rounded text-center bg-white shadow-sm d-flex flex-column justify-content-center h-100 py-4">
                                            <div class="text-muted fw-bold mb-2" style="font-size: 0.85rem;">mAP50</div>
                                            <div class="fw-bold text-success" style="font-size: 1.5rem;">{{ $model->map50 ? round($model->map50 * 100, 2) . '%' : 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 border rounded text-center bg-white shadow-sm d-flex flex-column justify-content-center h-100 py-4">
                                            <div class="text-muted fw-bold mb-2" style="font-size: 0.85rem;">mAP50-95</div>
                                            <div class="fw-bold text-dark" style="font-size: 1.5rem;">{{ $model->map50_95 ? round($model->map50_95 * 100, 2) . '%' : 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 border rounded text-center bg-white shadow-sm d-flex flex-column justify-content-center h-100 py-4">
                                            <div class="text-muted fw-bold mb-2" style="font-size: 0.85rem;">Precision</div>
                                            <div class="fw-bold text-dark" style="font-size: 1.5rem;">{{ $model->precision ? round($model->precision * 100, 2) . '%' : 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 border rounded text-center bg-white shadow-sm d-flex flex-column justify-content-center h-100 py-4">
                                            <div class="text-muted fw-bold mb-2" style="font-size: 0.85rem;">Recall</div>
                                            <div class="fw-bold text-dark" style="font-size: 1.5rem;">{{ $model->recall ? round($model->recall * 100, 2) . '%' : 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>

                </div>

                {{-- BAGIAN BAWAH: GALERI VISUAL DENGAN FILTER SENDIRI --}}
                <div class="card border-0 bg-light">
                    <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-2">
                        <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-images me-2"></i>Galeri Visual (Grafik & Batch)</h6>
                        
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                <i class="bi bi-funnel-fill me-1"></i> Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-filter-menu shadow">
                                @foreach($allImages as $filename => $path)
                                    @php $safeImg = Str::slug($filename); @endphp
                                    <li class="dropdown-item-custom">
                                        <input class="form-check-input filter-checkbox" type="checkbox" id="cb-img-{{$model->id}}-{{$safeImg}}" data-target=".item-img-{{$model->id}}-{{$safeImg}}" checked>
                                        <label class="form-check-label" for="cb-img-{{$model->id}}-{{$safeImg}}" title="{{$filename}}">{{ $filename }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card-body img-scroll-area">
                        <div class="img-grid">
                            @forelse($allImages as $filename => $path)
                                @php $safeImg = Str::slug($filename); @endphp
                                <div class="img-item item-img-{{$model->id}}-{{$safeImg}}">
                                    <div class="border rounded p-2 text-center bg-white shadow-sm h-100 d-flex flex-column">
                                        <a href="{{ asset('storage/' . $path) }}" target="_blank" class="flex-grow-1 d-flex align-items-center justify-content-center">
                                            <img src="{{ asset('storage/' . $path) }}" class="img-fluid rounded" style="max-height: 140px; object-fit: contain;">
                                        </a>
                                        <div class="mt-2 text-dark text-truncate fw-bold border-top pt-1" style="font-size: 0.75rem;" title="{{ $filename }}">{{ $filename }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-3">Tidak ada gambar yang tersimpan.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary px-4 fw-semibold rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
            
        </div>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logika untuk semua filter checkbox (YAML maupun Gambar)
        const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
        
        filterCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const targetSelector = this.getAttribute('data-target');
                const targetElements = document.querySelectorAll(targetSelector);
                
                targetElements.forEach(el => {
                    if (this.checked) {
                        el.style.display = ''; // Tampilkan kembali
                    } else {
                        el.style.display = 'none'; // Sembunyikan seketika
                    }
                });
            });
        });
    });
</script>
@endpush