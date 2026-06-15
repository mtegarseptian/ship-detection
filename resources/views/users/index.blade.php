@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')
@section('page-subtitle', 'Kelola akun pengguna sistem')

@section('content')

<div class="card">
    <div class="card-header p-3">
        <i class="bi bi-people me-2 text-primary"></i>Daftar Pengguna
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Total Deteksi</th>
                        <th>Bergabung</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,#6366f1,#8b5cf6); display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:.8rem; flex-shrink:0;">
                                    {{ strtoupper(substr($user->name,0,1)) }}
                                </div>
                                <span class="fw-600" style="font-size:.875rem;">{{ $user->name }}</span>
                                @if($user->id === auth()->id())
                                <span class="badge bg-info-subtle text-info rounded-pill" style="font-size:.65rem;">Anda</span>
                                @endif
                            </div>
                        </td>
                        <td style="font-size:.875rem;">{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->role === 'admin' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} rounded-pill">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-600">{{ $user->detections_count }}</span>
                            <span class="text-muted" style="font-size:.75rem;"> deteksi</span>
                        </td>
                        <td style="font-size:.8rem; color:#6c757d;">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="text-center">
                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="mb-0"
                                  onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger rounded-2">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @else
                            <span class="text-muted" style="font-size:.75rem;">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white">{{ $users->links() }}</div>
    @endif
</div>

@endsection