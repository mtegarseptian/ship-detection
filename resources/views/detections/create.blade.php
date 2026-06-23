@extends('layouts.app')
@section('title', 'Deteksi Kapal')
@section('page-title', 'Deteksi Kapal')
@section('page-subtitle', 'Upload citra satelit untuk proses deteksi')

@push('styles')
<style>
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
    .input-group-custom .form-select, .input-group-custom .btn {
        border: none;
    }
    .input-group-custom .form-select:focus {
        box-shadow: none;
    }
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
        width: 50px;
        height: 50px;
        background: white;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
    }
    .file-badge {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 2rem;
        padding: 0.4rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        margin-top: 1rem;
    }
    .btn-remove-img {
        transition: transform 0.2s;
    }
    .btn-remove-img:hover {
        transform: scale(1.1);
    }
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
                                
                                {{-- KEMBALIKAN TEKS INI: Teks "Klik untuk mengganti" --}}
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

{{-- MODAL DETAIL MODEL --}}
@foreach($activeModels as $model)
<div class="modal fade" id="modalDetail-{{ $model->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-light border-bottom-0 p-3">
                <h6 class="modal-title fw-bold mb-0">
                    <i class="bi bi-cpu text-primary me-2"></i>Detail Model: {{ $model->name }} 
                    <span class="badge bg-secondary ms-1">{{ $model->version }}</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100 bg-light">
                            <h6 class="fw-bold mb-2 text-primary" style="font-size:.9rem;"><i class="bi bi-gear-fill me-1"></i> Konfigurasi</h6>
                            <hr class="mt-1 mb-2">
                            <table class="table table-sm table-borderless mb-0" style="font-size: .85rem;">
                                <tr><td class="text-muted px-0">Base Model</td><td class="fw-semibold text-end px-0">{{ $model->base_model ?? 'N/A' }}</td></tr>
                                <tr><td class="text-muted px-0">Epochs</td><td class="fw-semibold text-end px-0">{{ $model->epochs ?? 'N/A' }}</td></tr>
                                <tr><td class="text-muted px-0">Batch Size</td><td class="fw-semibold text-end px-0">{{ $model->batch_size ?? 'N/A' }}</td></tr>
                                <tr><td class="text-muted px-0">Image Size</td><td class="fw-semibold text-end px-0">{{ $model->imgsz ?? 'N/A' }} px</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100 bg-light">
                            <h6 class="fw-bold mb-2 text-success" style="font-size:.9rem;"><i class="bi bi-check-circle-fill me-1"></i> Akurasi</h6>
                            <hr class="mt-1 mb-2">
                            <table class="table table-sm table-borderless mb-0" style="font-size: .85rem;">
                                <tr><td class="text-muted px-0">mAP50</td><td class="fw-bold text-success text-end px-0">{{ $model->map50 ? round($model->map50 * 100, 2) . '%' : 'N/A' }}</td></tr>
                                <tr><td class="text-muted px-0">mAP50-95</td><td class="fw-semibold text-end px-0">{{ $model->map50_95 ? round($model->map50_95 * 100, 2) . '%' : 'N/A' }}</td></tr>
                                <tr><td class="text-muted px-0">Precision</td><td class="fw-semibold text-end px-0">{{ $model->precision ? round($model->precision * 100, 2) . '%' : 'N/A' }}</td></tr>
                                <tr><td class="text-muted px-0">Recall</td><td class="fw-semibold text-end px-0">{{ $model->recall ? round($model->recall * 100, 2) . '%' : 'N/A' }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-2" style="font-size:.9rem;"><i class="bi bi-images me-1"></i> Grafik Evaluasi</h6>
                <div class="row g-2">
                    @if(!empty($model->metrics_images) && is_array($model->metrics_images))
                        @foreach($model->metrics_images as $namaGrafik => $path)
                        <div class="col-6 col-sm-4">
                            <div class="border rounded-2 p-1 text-center bg-light">
                                <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $path) }}" class="img-fluid rounded" alt="{{ $namaGrafik }}" style="max-height: 120px; object-fit: contain;">
                                </a>
                                <div class="mt-1 text-muted text-truncate" style="font-size:.7rem;" title="{{ $namaGrafik }}">{{ $namaGrafik }}</div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center py-3 bg-light rounded-2 border">
                            <span class="text-muted" style="font-size:.85rem;">Tidak ada grafik evaluasi.</span>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<script>
// --- Script Preview Gambar Interaktif & Ringkas ---
const satInput  = document.getElementById('satImage');
const dropZone  = document.getElementById('imageDropZone');
const preview   = document.getElementById('imagePreview');

// Kontainer State
const stateEmpty = document.getElementById('stateEmpty');
const statePreview = document.getElementById('statePreview');

// Teks Info File
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');

function showPreview(file) {
    const reader = new FileReader();
    reader.onload = e => {
        // Set Data Gambar
        preview.src = e.target.result;
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        
        // Sembunyikan state kosong, tampilkan preview
        stateEmpty.classList.add('d-none');
        statePreview.classList.remove('d-none');
        
        // Ubah padding & style kotak agar lebih fit
        dropZone.style.padding = '1rem';
        dropZone.style.borderStyle = 'solid';
        dropZone.style.borderColor = '#3b82f6';
        dropZone.style.backgroundColor = '#ffffff';
    };
    reader.readAsDataURL(file);
}

// FUNGSI BARU: Untuk menghapus gambar yang sudah dipilih
function removeImage(event) {
    // Mencegah klik menembus ke dropzone (yang akan membuka file dialog lagi)
    event.stopPropagation();
    
    // Kosongkan file input
    satInput.value = '';
    
    // Kembalikan ke tampilan awal (kosong)
    statePreview.classList.add('d-none');
    stateEmpty.classList.remove('d-none');
    
    // Kembalikan style dropzone ke default
    dropZone.style.padding = '1.5rem';
    dropZone.style.borderStyle = 'dashed';
    dropZone.style.borderColor = '#cbd5e1';
    dropZone.style.backgroundColor = '#f8fafc';
}

satInput.addEventListener('change', () => { 
    if (satInput.files[0]) showPreview(satInput.files[0]); 
});

// Efek Drag & Drop
['dragover','dragenter'].forEach(e => dropZone.addEventListener(e, ev => { 
    ev.preventDefault(); 
    dropZone.classList.add('dragover'); 
}));

['dragleave','drop'].forEach(e => dropZone.addEventListener(e, () => {
    dropZone.classList.remove('dragover');
}));

dropZone.addEventListener('drop', ev => {
    ev.preventDefault();
    const f = ev.dataTransfer.files[0];
    if (f) { 
        satInput.files = ev.dataTransfer.files; 
        showPreview(f); 
    }
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
</script>
@endpush