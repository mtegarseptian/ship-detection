@extends('layouts.app')
@section('title', 'Deteksi Kapal')
@section('page-title', 'Deteksi Kapal')
@section('page-subtitle', 'Upload citra satelit untuk proses deteksi')

@section('content')

@if(!$activeModel)
<div class="alert alert-warning d-flex gap-2 align-items-start">
    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
    <div>
        <strong>Tidak ada model AI aktif!</strong><br>
        <span style="font-size:.875rem;">Aktifkan model AI terlebih dahulu di menu
        <a href="{{ route('models.index') }}">Model AI</a>.</span>
    </div>
</div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header p-3">
                <i class="bi bi-image me-2 text-primary"></i>Upload Citra Satelit
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $e)
                    <div style="font-size:.875rem;"><i class="bi bi-x-circle me-1"></i>{{ $e }}</div>
                    @endforeach
                </div>
                @endif

                <form action="{{ route('detections.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Model Aktif Info --}}
                    <div class="p-3 rounded-3 mb-4"
                         style="background:#f0f6ff; border:1px solid #cfe2ff;">
                        <div class="d-flex align-items-center gap-2" style="font-size:.875rem;">
                            <i class="bi bi-cpu-fill text-primary"></i>
                            <span><strong>Model Aktif:</strong>
                                {{ $activeModel ? $activeModel->name . ' ' . ($activeModel->version ?? '') : 'Tidak ada' }}
                            </span>
                            @if($activeModel)
                            <span class="badge badge-active rounded-pill ms-1">Aktif</span>
                            @endif
                        </div>
                    </div>

                    {{-- Drop Zone --}}
                    <div class="mb-4">
                        <label class="form-label fw-600" style="font-size:.875rem;">Citra Satelit <span class="text-danger">*</span></label>
                        <div class="upload-zone" id="imageDropZone" onclick="document.getElementById('satImage').click()">
                            <div class="upload-icon" id="uploadIcon"><i class="bi bi-cloud-upload"></i></div>
                            <p class="mt-2 mb-1 fw-600" id="uploadText">Klik atau seret citra satelit ke sini</p>
                            <p class="text-muted mb-0" id="uploadSub" style="font-size:.8rem;">Format: JPG, PNG, TIFF — Maks. 20 MB</p>
                            <img id="imagePreview" src="" alt="" style="display:none; max-height:200px; border-radius:8px; margin-top:1rem; max-width:100%;">
                        </div>
                        <input type="file" id="satImage" name="satellite_image"
                               accept="image/jpeg,image/png,image/tiff,.tiff,.tif"
                               class="d-none" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-3 px-4" {{ !$activeModel ? 'disabled' : '' }}>
                            <i class="bi bi-search me-2"></i>Mulai Deteksi
                        </button>
                        <a href="{{ route('detections.index') }}" class="btn btn-light rounded-3">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const satInput  = document.getElementById('satImage');
const dropZone  = document.getElementById('imageDropZone');
const preview   = document.getElementById('imagePreview');
const uploadIcon = document.getElementById('uploadIcon');
const uploadText = document.getElementById('uploadText');
const uploadSub  = document.getElementById('uploadSub');

function showPreview(file) {
    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
        uploadIcon.style.display = 'none';
        uploadText.textContent  = '✓ ' + file.name;
        uploadSub.textContent   = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    };
    reader.readAsDataURL(file);
}

satInput.addEventListener('change', () => { if (satInput.files[0]) showPreview(satInput.files[0]); });

['dragover','dragenter'].forEach(e => dropZone.addEventListener(e, ev => { ev.preventDefault(); dropZone.classList.add('dragover'); }));
['dragleave','drop'].forEach(e => dropZone.addEventListener(e, () => dropZone.classList.remove('dragover')));
dropZone.addEventListener('drop', ev => {
    ev.preventDefault();
    const f = ev.dataTransfer.files[0];
    if (f) { satInput.files = ev.dataTransfer.files; showPreview(f); }
});
</script>
@endpush