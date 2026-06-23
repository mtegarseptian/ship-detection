@extends('layouts.app')
@section('title', 'Upload Paket Model AI')
@section('page-title', 'Upload Paket Model AI')
@section('page-subtitle', 'Tambahkan paket hasil training model AI baru ke sistem')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-3 border-bottom">
                <i class="bi bi-file-earmark-zip me-2 text-primary"></i>Upload Paket Hasil Training (.zip)
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $e)
                        <li style="font-size:.875rem;">{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('models.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.875rem;">Nama Model <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3"
                               placeholder="cth: YOLOv8 OBB Kapal" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.875rem;">Versi</label>
                        <input type="text" name="version" class="form-control rounded-3"
                               placeholder="cth: v1" value="{{ old('version') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.875rem;">Deskripsi</label>
                        <textarea name="description" class="form-control rounded-3" rows="3"
                                  placeholder="Deskripsi singkat tentang model ini...">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-600" style="font-size:.875rem;">File Paket Model (.zip) <span class="text-danger">*</span></label>
                        <div class="upload-zone p-4 text-center border rounded-3 bg-light" id="modelDropZone" onclick="document.getElementById('modelFile').click()" style="cursor: pointer; border-style: dashed !important; border-width: 2px !important;">
                            <div class="upload-icon text-primary mb-2"><i class="bi bi-file-earmark-zip" style="font-size: 2rem;"></i></div>
                            <p class="mb-1 fw-600">Klik atau seret file ZIP ke sini</p>
                            <p class="text-muted mb-0" style="font-size:.8rem;">Format: <strong>.zip</strong> — Maks. 512 MB</p>
                            <div id="fileNameDisplay" class="mt-2 text-success fw-600" style="font-size:.875rem; display:none;"></div>
                        </div>
                        <input type="file" id="modelFile" name="model_zip"
                               accept=".zip" class="d-none" required>
                        
                        <div class="form-text mt-2" style="font-size:.8rem;">
                            <i class="bi bi-info-circle text-primary me-1"></i> Compress seluruh isi folder hasil training YOLO (<code>runs/train/...</code>) menjadi satu file <strong>.zip</strong>. Sistem akan mengekstrak model <code>best.pt</code> dan grafik evaluasi secara otomatis.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-600">
                            <i class="bi bi-cloud-upload me-2"></i>Upload & Ekstrak
                        </button>
                        <a href="{{ route('models.index') }}" class="btn btn-light rounded-3 fw-600 border">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const fileInput = document.getElementById('modelFile');
const dropZone  = document.getElementById('modelDropZone');
const nameDisp  = document.getElementById('fileNameDisplay');

fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) {
        nameDisp.textContent = '✓ ' + fileInput.files[0].name;
        nameDisp.style.display = 'block';
    }
});

['dragover','dragenter'].forEach(e => dropZone.addEventListener(e, ev => { ev.preventDefault(); dropZone.classList.add('border-primary'); }));
['dragleave','drop'].forEach(e => dropZone.addEventListener(e, () => dropZone.classList.remove('border-primary')));
dropZone.addEventListener('drop', ev => {
    ev.preventDefault();
    if (ev.dataTransfer.files[0]) {
        fileInput.files = ev.dataTransfer.files;
        nameDisp.textContent = '✓ ' + ev.dataTransfer.files[0].name;
        nameDisp.style.display = 'block';
    }
});
</script>
@endpush