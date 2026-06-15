<?php

namespace App\Http\Controllers;

use App\Models\ModelAI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ModelAIController extends Controller
{
    public function index()
    {
        $models = ModelAI::with('uploader')->latest()->paginate(10);
        return view('models.index', compact('models'));
    }

    public function create()
    {
        return view('models.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'version'     => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'model_file'  => 'required|file|mimes:h5,pt,onnx,bin|max:512000',
        ]);

        $file     = $request->file('model_file');
        $ext      = $file->getClientOriginalExtension();
        $path     = $file->store('models', 'public');

        ModelAI::create([
            'name'        => $request->name,
            'version'     => $request->version,
            'description' => $request->description,
            'file_path'   => $path,
            'file_type'   => $ext,
            'status'      => 'inactive',
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('models.index')
            ->with('success', 'Model AI berhasil diunggah!');
    }

    public function toggleStatus(ModelAI $model)
    {
        // Nonaktifkan semua dulu, lalu aktifkan yang dipilih
        if ($model->status === 'inactive') {
            ModelAI::where('status', 'active')->update(['status' => 'inactive']);
            $model->update(['status' => 'active']);
            $msg = "Model '{$model->name}' diaktifkan.";
        } else {
            $model->update(['status' => 'inactive']);
            $msg = "Model '{$model->name}' dinonaktifkan.";
        }

        return back()->with('success', $msg);
    }

    public function destroy(ModelAI $model)
    {
        Storage::disk('public')->delete($model->file_path);
        $model->delete();
        return back()->with('success', 'Model berhasil dihapus.');
    }
}