@extends('layouts.app')
@section('title', 'Hasil Deteksi #' . $detection->id)
@section('page-title', 'Hasil Deteksi')
@section('page-subtitle', 'Detail proses deteksi kapal #' . $detection->id)

@section('content')

<div class="row g-3">
    {{-- Info Panel --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header p-3">
                <i class="bi bi-info-circle me-2 text-primary"></i>Informasi Deteksi
            </div>
            <div class="card-body p-3">
                <table class="table table-sm mb-0" style="font-size:.875rem;">
                    <tr>
                        <td class="text-muted fw-500 border-0" style="width:45%;">ID</td>
                        <td class="border-0 fw-600">#{{ $detection->id }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-500">Status</td>
                        <td>
                            <span class="badge badge-{{ $detection->status }} rounded-pill">
                                {{ ucfirst($detection->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-500">Model AI</td>
                        <td class="fw-600">{{ $detection->modelAI->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-500">Kapal</td>
                        <td class="fw-600 text-primary" style="font-size:1.1rem;">{{ $detection->ship_count }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-500">Operator</td>
                        <td>{{ $detection->user->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-500">Waktu</td>
                        <td>{{ $detection->created_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($detection->status === 'pending')
        <div class="alert alert-warning d-flex gap-2" style="font-size:.875rem;">
            <i class="bi bi-hourglass-split mt-1"></i>
            <div>Citra sedang dalam antrian pemrosesan AI.</div>
        </div>
        @endif

        <a href="{{ route('detections.index') }}" class="btn btn-light rounded-3 w-100">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Riwayat
        </a>
    </div>

    {{-- Image Viewer --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header p-3 d-flex gap-2">
                <button class="btn btn-sm btn-primary rounded-2 active" id="btnOriginal" onclick="switchImg('original')">
                    Citra Asli
                </button>
                <button class="btn btn-sm btn-outline-primary rounded-2" id="btnResult" onclick="switchImg('result')"
                        {{ !$detection->image_result ? 'disabled' : '' }}>
                    Hasil Deteksi
                </button>
            </div>
            <div class="card-body p-3 text-center">
                <img id="imgOriginal"
                     src="{{ Storage::url($detection->image_original) }}"
                     class="img-fluid rounded-3"
                     style="max-height:450px; width:100%; object-fit:contain; background:#f8f9fa;">
                <img id="imgResult"
                     src="{{ $detection->image_result ? Storage::url($detection->image_result) : '' }}"
                     class="img-fluid rounded-3 d-none"
                     style="max-height:450px; width:100%; object-fit:contain; background:#f8f9fa;">
            </div>
        </div>

        @if($detection->bounding_boxes && count($detection->bounding_boxes) > 0)
        <div class="card mt-3">
            <div class="card-header p-3">
                <i class="bi bi-bounding-box me-2 text-primary"></i>Data Bounding Box
                <span class="badge bg-primary rounded-pill ms-1">{{ count($detection->bounding_boxes) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size:.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>x1</th><th>y1</th><th>x2</th><th>y2</th>
                                <th>Konfidensial</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detection->bounding_boxes as $i => $box)
                            <tr>
                                <td class="ps-3">{{ $i+1 }}</td>
                                <td>{{ $box['x1'] ?? '-' }}</td>
                                <td>{{ $box['y1'] ?? '-' }}</td>
                                <td>{{ $box['x2'] ?? '-' }}</td>
                                <td>{{ $box['y2'] ?? '-' }}</td>
                                <td>
                                    <div class="progress" style="height:16px; width:80px;">
                                        <div class="progress-bar bg-success" style="width:{{ ($box['confidence'] ?? 0) * 100 }}%">
                                            {{ number_format(($box['confidence'] ?? 0) * 100, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function switchImg(type) {
    const orig   = document.getElementById('imgOriginal');
    const result = document.getElementById('imgResult');
    const btnO   = document.getElementById('btnOriginal');
    const btnR   = document.getElementById('btnResult');

    if (type === 'original') {
        orig.classList.remove('d-none');
        result.classList.add('d-none');
        btnO.classList.replace('btn-outline-primary', 'btn-primary');
        btnR.classList.replace('btn-primary', 'btn-outline-primary');
    } else {
        orig.classList.add('d-none');
        result.classList.remove('d-none');
        btnR.classList.replace('btn-outline-primary', 'btn-primary');
        btnO.classList.replace('btn-primary', 'btn-outline-primary');
    }
}
</script>
@endpush