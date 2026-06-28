<?php

namespace App\Http\Controllers;

use App\Models\Detection;
use App\Models\ModelAI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DetectionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Detection::with(['user', 'modelAI'])->latest();
        $operators = [];

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        } else {
            $operators = \App\Models\User::withCount('detections')->having('detections_count', '>', 0)->get();

            if ($request->filled('operator_id')) {
                $query->where('user_id', $request->operator_id);
            }
        }

        $detections = $query->paginate(10);
        $detections->appends($request->all()); 

        return view('detections.index', compact('detections', 'operators'));
    }

    public function create()
    {
        $activeModels = ModelAI::query()->where('status', 'active')->get();
        return view('detections.create', compact('activeModels'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input, termasuk nilai slider dari Frontend
        $request->validate([
            'satellite_image'      => 'required|image|mimes:jpg,jpeg,png,tiff|max:20480',
            'model_ai_id'          => 'required|exists:model_a_i_s,id',
            'confidence_threshold' => 'required|numeric|min:0.05|max:0.95', // Validasi Slider
        ], [
            'model_ai_id.required' => 'Anda harus memilih Model AI yang akan digunakan.'
        ]);

        // 2. Simpan Gambar Asli
        $path = $request->file('satellite_image')->store('detections/original', 'public');

        // 3. Simpan ke Database
        $detection = Detection::create([
            'image_original'       => $path,
            'model_ai_id'          => $request->model_ai_id,
            'user_id'              => Auth::id(),
            'status'               => 'pending',
            'ship_count'           => 0,
            'confidence_threshold' => $request->confidence_threshold, // Tangkap data Slider
        ]);

        // Nanti di sinilah letak kode "shell_exec('python detect.py...')" untuk memanggil AI.
        // Untuk sementara, kita biarkan statusnya 'pending'.

        return redirect()->route('detections.show', $detection)
            ->with('success', 'Citra berhasil diunggah! Menunggu pemrosesan AI.');
    }

    public function show(Detection $detection)
    {
        if (Auth::id() !== $detection->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak berhak melihat data ini.');
        }

        $detection->load(['user', 'modelAI']);
        return view('detections.show', compact('detection'));
    }

    public function destroy(Detection $detection)
    {
        if (Auth::id() !== $detection->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Anda hanya dapat menghapus riwayat milik sendiri.');
        }

        if ($detection->image_original) {
            Storage::disk('public')->delete($detection->image_original);
        }
        if ($detection->image_result) {
            Storage::disk('public')->delete($detection->image_result);
        }

        $detection->delete();

        return redirect()->route('detections.index')
            ->with('success', 'Riwayat deteksi beserta file gambarnya berhasil dihapus permanen.');
    }

    public function clear()
    {
        $user = Auth::user();
        $query = Detection::query();

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $detections = $query->get();

        foreach ($detections as $d) {
            if ($d->image_original) {
                Storage::disk('public')->delete($d->image_original);
            }
            if ($d->image_result) {
                Storage::disk('public')->delete($d->image_result);
            }
            $d->delete();
        }

        $pesan = $user->isAdmin() ? 'Seluruh riwayat deteksi di server berhasil dibersihkan.' : 'Semua riwayat deteksi Anda berhasil dihapus.';
        return back()->with('success', $pesan);
    }
}