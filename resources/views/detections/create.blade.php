@extends('layouts.app')
@section('title', 'Deteksi Kapal')
@section('page-title', 'Deteksi Kapal')
@section('page-subtitle', 'Upload citra satelit untuk proses deteksi')

@push('styles')
<style>
    /* --- CSS Lama: Form & Upload Zone --- */
    .card-custom {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .input-group-custom {
        border-radius: 0.5rem;
        border: 1px solid #cbd5e1;
        overflow: hidden;
    }
    .input-group-custom .form-select, .input-group-custom .btn { border: none; }
    .input-group-custom .form-select:focus { box-shadow: none; }
    .input-group-custom:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .upload-zone {
        border: 2px dashed #cbd5e1;
        background-color: #f8fafc;
        border-radius: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 6rem 2rem; 
        text-align: center;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
    .upload-icon-circle {
        width: 50px; height: 50px; background: white; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;
    }
    .file-badge {
        background: white; border: 1px solid #e2e8f0; border-radius: 2rem;
        padding: 0.4rem 1rem; display: inline-flex; align-items: center;
        gap: 0.5rem; font-size: 0.85rem; margin-top: 1rem;
    }
    .btn-remove-img { transition: transform 0.2s; }
    .btn-remove-img:hover { transform: scale(1.1); }

    /* --- CSS Baru: Modal Detail Model AI --- */
    .param-scroll-area { max-height: 280px; overflow-y: auto; padding-right: 10px; }
    .param-scroll-area::-webkit-scrollbar { width: 6px; }
    .param-scroll-area::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
    .param-scroll-area::-webkit-scrollbar-track { background-color: transparent; }
    
    .img-scroll-area { max-height: 350px; overflow-y: auto; overflow-x: hidden; padding-right: 10px; }
    .img-scroll-area::-webkit-scrollbar { width: 6px; }
    .img-scroll-area::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

    .dropdown-filter-menu {
        min-width: 280px; max-height: 300px; overflow-y: auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: 1px solid #e2e8f0;
        border-radius: 0.5rem; padding: 0.5rem 0;
    }
    .dropdown-filter-menu::-webkit-scrollbar { width: 5px; }
    .dropdown-filter-menu::-webkit-scrollbar-thumb { background-color: #94a3b8; border-radius: 10px; }
    
    .dropdown-item-custom {
        padding: 0.35rem 1.25rem; cursor: pointer; transition: background 0.2s;
        display: flex; align-items: center; gap: 0.5rem;
    }
    .dropdown-item-custom:hover { background-color: #f1f5f9; }
    .form-check-input { cursor: pointer; margin-top: 0; }
    .form-check-label { cursor: pointer; font-size: 0.85rem; user-select: none; width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .param-item { border-bottom: 1px dashed #e2e8f0; padding: 10px 0; display: flex; align-items: start; justify-content: space-between; transition: all 0.2s; }
    .param-item:last-child { border-bottom: none; }
    .param-name { font-weight: 600; color: #1e293b; font-size: 0.85rem; font-family: monospace; }
    .param-val { background: #e0e7ff; color: #4338ca; padding: 2px 10px; border-radius: 6px; font-size: 0.8rem; font-family: monospace; font-weight: bold; max-width: 60%; word-break: break-all; text-align: right;}
    
    .img-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
    .img-item { transition: all 0.3s ease; }
</style>
@endpush

@section('content')

@if($activeModels->isEmpty())
<div class="alert alert-warning d-flex gap-2 align-items-start border-0 shadow-sm rounded-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill mt-1 text-warning"></i>
    <div>
        <strong>Tidak ada model AI aktif!</strong><br>
        <span style="font-size:.875rem;">Pilih atau aktifkan model AI terlebih dahulu di menu 
        @can('admin') <a href="{{ route('models.index') }}" class="text-decoration-none fw-bold">Model AI</a> @else hubungi Admin. @endcan
        </span>
    </div>
</div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom bg-white">
            <div class="card-header bg-transparent p-3 border-bottom d-flex align-items-center gap-2">
                <i class="bi bi-image text-primary fs-5"></i>
                <h6 class="mb-0 fw-bold">Upload Citra Satelit</h6>
            </div>
            
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger rounded-3 p-3 mb-4" style="font-size:.875rem;">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('detections.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- 1. Pilih Model --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.9rem;">
                            1. Pilih Model AI <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-custom">
                            <span class="input-group-text bg-light text-muted border-0"><i class="bi bi-cpu"></i></span>
                            <select name="model_ai_id" id="modelSelect" class="form-select bg-light" required {{ $activeModels->isEmpty() ? 'disabled' : '' }}>
                                <option value="" disabled selected>-- Pilih Model Deteksi --</option>
                                @foreach($activeModels as $model)
                                    <option value="{{ $model->id }}">
                                        {{ $model->name }} (Versi: {{ $model->version ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary px-3 fw-medium border-start" type="button" id="btnDetailModel" disabled>
                                <i class="bi bi-info-circle me-1"></i> Detail
                            </button>
                        </div>
                    </div>

                    {{-- 2. Drop Zone Citra --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.9rem;">
                            2. Citra Satelit <span class="text-danger">*</span>
                        </label>
                        
                        <div class="upload-zone" id="imageDropZone" onclick="document.getElementById('satImage').click()">
                            
                            {{-- State: Awal (Belum ada gambar) --}}
                            <div id="stateEmpty">
                                <div class="upload-icon-circle">
                                    <i class="bi bi-cloud-arrow-up-fill fs-4 text-primary"></i>
                                </div>
                                <h6 class="fw-semibold text-dark mb-1">Klik atau seret citra ke sini</h6>
                                <p class="text-muted mb-0" style="font-size:.8rem;">Format: JPG, PNG, TIFF (Maks. 20 MB)</p>
                            </div>

                            {{-- State: Preview (Gambar sudah dipilih) --}}
                            <div id="statePreview" class="d-none">
                                <div class="position-relative d-inline-block">
                                    {{-- Tombol X untuk Batal --}}
                                    <button type="button" class="btn btn-danger position-absolute rounded-circle shadow btn-remove-img" onclick="removeImage(event)" style="top: -12px; right: -12px; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; z-index: 10;" title="Batal / Hapus Gambar">
                                        <i class="bi bi-x fs-5"></i>
                                    </button>
                                    
                                    <img id="imagePreview" src="" alt="Preview" class="img-fluid rounded-2 shadow-sm" style="max-height: 180px; object-fit: contain;">
                                </div>
                                <div class="d-block mt-1">
                                    <div class="file-badge shadow-sm">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span id="fileName" class="fw-semibold text-dark text-truncate" style="max-width: 150px;">-</span>
                                        <span id="fileSize" class="text-muted border-start ps-2">-</span>
                                    </div>
                                </div>
                                
                                <div class="mt-2 text-primary fw-semibold" style="font-size: 0.85rem;">
                                    <i class="bi bi-arrow-repeat me-1"></i>Klik area ini untuk mengganti gambar
                                </div>
                                
                            </div>
                        </div>
                        <input type="file" id="satImage" name="satellite_image" accept="image/jpeg,image/png,image/tiff,.tiff,.tif" class="d-none" required {{ $activeModels->isEmpty() ? 'disabled' : '' }}>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex gap-2 justify-content-end pt-2">
                        <a href="{{ route('detections.index') }}" class="btn btn-light px-4 rounded-3 fw-medium">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 rounded-3 fw-medium" {{ $activeModels->isEmpty() ? 'disabled' : '' }}>
                            <i class="bi bi-radar me-1"></i> Mulai Deteksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

{{-- ================= MODAL LOOPING DETAIL MODEL ================= --}}
@foreach($activeModels as $model)

@php
    $rawYamlData = is_array($model->args_yaml) ? $model->args_yaml : [];
    $yamlData = [];
    
    // Urutkan data sesuai array original
    foreach($orderedKeys as $key) {
        if(array_key_exists($key, $rawYamlData)) {
            $yamlData[$key] = $rawYamlData[$key];
            unset($rawYamlData[$key]);
        }
    }
    // Sisa data
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
                                                <input class="form-check-input filter-checkbox" type="checkbox" id="cb-yaml-det-{{$model->id}}-{{$safeKey}}" data-target=".item-yaml-det-{{$model->id}}-{{$safeKey}}" checked>
                                                <label class="form-check-label" for="cb-yaml-det-{{$model->id}}-{{$safeKey}}" title="{{ $key }}">{{ $key }}</label>
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
                                    <div class="param-item item-yaml-det-{{$model->id}}-{{$safeKey}}">
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

                {{-- BAGIAN BAWAH: GALERI VISUAL DENGAN FILTER --}}
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
                                        <input class="form-check-input filter-checkbox" type="checkbox" id="cb-img-det-{{$model->id}}-{{$safeImg}}" data-target=".item-img-det-{{$model->id}}-{{$safeImg}}" checked>
                                        <label class="form-check-label" for="cb-img-det-{{$model->id}}-{{$safeImg}}" title="{{$filename}}">{{ $filename }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card-body img-scroll-area">
                        <div class="img-grid">
                            @forelse($allImages as $filename => $path)
                                @php $safeImg = Str::slug($filename); @endphp
                                <div class="img-item item-img-det-{{$model->id}}-{{$safeImg}}">
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
// --- Script Preview Gambar Interaktif ---
const satInput  = document.getElementById('satImage');
const dropZone  = document.getElementById('imageDropZone');
const preview   = document.getElementById('imagePreview');

const stateEmpty = document.getElementById('stateEmpty');
const statePreview = document.getElementById('statePreview');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');

function showPreview(file) {
    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        
        stateEmpty.classList.add('d-none');
        statePreview.classList.remove('d-none');
        
        dropZone.style.padding = '1rem';
        dropZone.style.borderStyle = 'solid';
        dropZone.style.borderColor = '#3b82f6';
        dropZone.style.backgroundColor = '#ffffff';
    };
    reader.readAsDataURL(file);
}

function removeImage(event) {
    event.stopPropagation();
    satInput.value = '';
    statePreview.classList.add('d-none');
    stateEmpty.classList.remove('d-none');
    
    dropZone.style.padding = '1.5rem';
    dropZone.style.borderStyle = 'dashed';
    dropZone.style.borderColor = '#cbd5e1';
    dropZone.style.backgroundColor = '#f8fafc';
}

satInput.addEventListener('change', () => { 
    if (satInput.files[0]) showPreview(satInput.files[0]); 
});

['dragover','dragenter'].forEach(e => dropZone.addEventListener(e, ev => { 
    ev.preventDefault(); dropZone.classList.add('dragover'); 
}));

['dragleave','drop'].forEach(e => dropZone.addEventListener(e, () => {
    dropZone.classList.remove('dragover');
}));

dropZone.addEventListener('drop', ev => {
    ev.preventDefault();
    const f = ev.dataTransfer.files[0];
    if (f) { satInput.files = ev.dataTransfer.files; showPreview(f); }
});

// --- Script Tombol Detail Model ---
const modelSelect = document.getElementById('modelSelect');
const btnDetail = document.getElementById('btnDetailModel');

if(modelSelect) {
    modelSelect.addEventListener('change', function() {
        if(this.value) {
            btnDetail.removeAttribute('disabled');
            btnDetail.setAttribute('data-bs-toggle', 'modal');
            btnDetail.setAttribute('data-bs-target', '#modalDetail-' + this.value);
        }
    });
}

// --- Script Filter Modal Detail ---
const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
filterCheckboxes.forEach(cb => {
    cb.addEventListener('change', function() {
        const targetSelector = this.getAttribute('data-target');
        const targetElements = document.querySelectorAll(targetSelector);
        
        targetElements.forEach(el => {
            if (this.checked) {
                el.style.display = ''; 
            } else {
                el.style.display = 'none'; 
            }
        });
    });
});
</script>
@endpush