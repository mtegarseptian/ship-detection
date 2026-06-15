@extends('layouts.app')
@section('title', 'Upload Model AI')
@section('page-title', 'Upload Model AI')
@section('page-subtitle', 'Tambahkan model AI baru ke sistem')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header p-3">
                <i class="bi bi-cloud-upload me-2 text-primary"></i>Upload Model AI Baru
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger">
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
                               placeholder="cth: YOLOv8-ShipDetect" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.875rem;">Versi</label>
                        <input type="text" name="version" class="form-control rounded-3"
                               placeholder="cth: v1.0.0" value="{{ old('version') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.875rem;">Deskripsi</label>
                        <textarea name="description" class="form-control rounded-3" rows="3"
                                  placeholder="Deskripsi singkat tentang model...">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-600" style="font-size:.875rem;">File Model <span class="text-danger">*</span></label>
                        <div class="upload-zone" id="modelDropZone" onclick="document.getElementById('modelFile').click()">
                            <div class="upload-icon"><i class="bi bi-file-earmark-binary"></i></div>
                            <p class="mt-2 mb-1 fw-600">Klik atau seret file model ke sini</p>
                            <p class="text-muted mb-0" style="font-size:.8rem;">Format: .h5 / .pt / .onnx / .bin — Maks. 512 MB</p>
                            <div id="fileNameDisplay" class="mt-2 text-primary fw-600" style="font-size:.875rem; display:none;"></div>
                        </div>
                        <input type="file" id="modelFile" name="model_file"
                               accept=".h5,.pt,.onnx,.bin" class="d-none" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-3 px-4">
                            <i class="bi bi-cloud-upload me-2"></i>Upload Model
                        </button>
                        <a href="{{ route('models.index') }}" class="btn btn-light rounded-3">Batal</a>
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

['dragover','dragenter'].forEach(e => dropZone.addEventListener(e, ev => { ev.preventDefault(); dropZone.classList.add('dragover'); }));
['dragleave','drop'].forEach(e => dropZone.addEventListener(e, () => dropZone.classList.remove('dragover')));
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