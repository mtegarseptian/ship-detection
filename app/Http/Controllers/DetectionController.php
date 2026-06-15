<?php

namespace App\Http\Controllers;

use App\Models\Detection;
use App\Models\ModelAI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetectionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $detections = Detection::with(['user', 'modelAI'])
            ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->paginate(10);

        return view('detections.index', compact('detections'));
    }

    public function create()
    {
        $activeModel = ModelAI::where('status', 'active')->first();
        return view('detections.create', compact('activeModel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'satellite_image' => 'required|image|mimes:jpg,jpeg,png,tiff|max:20480',
        ]);

        $activeModel = ModelAI::where('status', 'active')->first();

        $path = $request->file('satellite_image')->store('detections/original', 'public');

        $detection = Detection::create([
            'image_original' => $path,
            'model_ai_id'    => $activeModel?->id,
            'user_id'        => Auth::id(),
            'status'         => 'pending',
            'ship_count'     => 0,
        ]);

        // TODO: Kirim ke AI service (nanti)
        // Sementara set status jadi "pending"

        return redirect()->route('detections.show', $detection)
            ->with('success', 'Citra berhasil diunggah! Menunggu pemrosesan AI.');
    }

    public function show(Detection $detection)
    {
        $this->authorize('view', $detection);
        $detection->load(['user', 'modelAI']);
        return view('detections.show', compact('detection'));
    }
}