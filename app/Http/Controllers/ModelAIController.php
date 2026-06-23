<?php

namespace App\Http\Controllers;

use App\Models\ModelAI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ModelAIController extends Controller
{
    public function index()
    {
        $models = ModelAI::query()->with('uploader')->latest()->paginate(10);
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
            'model_zip'   => 'required|file|mimes:zip|max:512000', // Wajib file ZIP
        ]);

        $zipFile = $request->file('model_zip');
        $extractPath = storage_path('app/public/temp_extract_' . time());
        
        $zip = new \ZipArchive;
        if ($zip->open($zipFile->getPathname()) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            return back()->with('error', 'Gagal membuka file ZIP. Pastikan file tidak korup.');
        }

        // 1. Cari file best.pt
        $bestPtFiles = File::glob($extractPath . '/**/weights/best.pt');
        if (empty($bestPtFiles)) {
            File::deleteDirectory($extractPath);
            return back()->with('error', 'File weights/best.pt tidak ditemukan di dalam ZIP.');
        }
        $bestPtPath = $bestPtFiles[0];
        
        // Pindahkan best.pt ke storage permanen
        $modelStoragePath = 'models/' . uniqid() . '_best.pt';
        Storage::disk('public')->put($modelStoragePath, file_get_contents($bestPtPath));

        // 2. Ekstrak data dari args.yaml
        $argsData = ['model' => null, 'epochs' => null, 'batch' => null, 'imgsz' => null];
        $yamlFiles = File::glob($extractPath . '/**/args.yaml');
        if (!empty($yamlFiles)) {
            $yamlContent = file_get_contents($yamlFiles[0]);
            preg_match('/model:\s*(.*)/', $yamlContent, $m); $argsData['model'] = trim($m[1] ?? '-');
            preg_match('/epochs:\s*([0-9]+)/', $yamlContent, $m); $argsData['epochs'] = $m[1] ?? null;
            preg_match('/batch:\s*([0-9]+)/', $yamlContent, $m); $argsData['batch'] = $m[1] ?? null;
            preg_match('/imgsz:\s*([0-9]+)/', $yamlContent, $m); $argsData['imgsz'] = $m[1] ?? null;
        }

        // 3. Ekstrak data akurasi dari results.csv (Ambil baris terakhir)
        $csvData = ['precision' => 0, 'recall' => 0, 'map50' => 0, 'map50_95' => 0];
        $csvFiles = File::glob($extractPath . '/**/results.csv');
        if (!empty($csvFiles)) {
            $lines = file($csvFiles[0], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lastLine = str_getcsv(array_pop($lines)); 
            // Urutan index kolom YOLOv8 standar: 4=Precision, 5=Recall, 6=mAP50, 7=mAP50-95
            if (count($lastLine) >= 7) {
                $csvData['precision'] = (float)($lastLine[4] ?? 0);
                $csvData['recall']    = (float)($lastLine[5] ?? 0);
                $csvData['map50']     = (float)($lastLine[6] ?? 0);
                $csvData['map50_95']  = (float)($lastLine[7] ?? 0);
            }
        }

        // 4. Ambil Grafik Evaluasi (Mendukung YOLO Standar & YOLO OBB)
        $grafikDisimpan = [];
        
        // PERBAIKAN: Masukkan semua variasi nama file (Standard & Box OBB)
        $grafikTarget = [
            // Versi Standar
            'confusion_matrix.png',
            'confusion_matrix_normalized.png',
            'F1_curve.png',
            'P_curve.png',
            'PR_curve.png',
            'R_curve.png',
            
            // Versi YOLO OBB (Sering keluar di deteksi kapal)
            'BoxF1_curve.png',
            'BoxP_curve.png',
            'BoxPR_curve.png',
            'BoxR_curve.png',
            
            // Rangkuman & Label
            'results.png',
            'labels.jpg',
            'labels_correlogram.jpg'
        ];
        
        $semuaGambar = File::allFiles($extractPath);
        $folderGrafik = 'models/metrics/' . uniqid() . '/';
        
        foreach ($semuaGambar as $file) {
            $namaFile = $file->getFilename();
            
            // Cek apakah nama file dari ZIP cocok (case-insensitive agar lebih aman)
            if (in_array($namaFile, $grafikTarget)) {
                $pathTujuan = $folderGrafik . $namaFile;
                Storage::disk('public')->put($pathTujuan, file_get_contents($file->getRealPath()));
                $grafikDisimpan[$namaFile] = $pathTujuan;
            }
        }

        // Hapus folder temp ekstrak agar storage tidak penuh
        File::deleteDirectory($extractPath);

        // 5. Simpan ke Database
        ModelAI::create([
            'name'           => $request->name,
            'version'        => $request->version,
            'description'    => $request->description,
            'file_path'      => $modelStoragePath,
            'file_type'      => 'pt',
            'status'         => 'active', // Otomatis aktif
            'uploaded_by'    => Auth::id(),
            
            // Konfigurasi YAML
            'base_model'     => $argsData['model'],
            'epochs'         => $argsData['epochs'],
            'batch_size'     => $argsData['batch'],
            'imgsz'          => $argsData['imgsz'],
            
            // Metrik CSV
            'precision'      => $csvData['precision'],
            'recall'         => $csvData['recall'],
            'map50'          => $csvData['map50'],
            'map50_95'       => $csvData['map50_95'],
            
            // File Grafik
            'metrics_images' => $grafikDisimpan,
        ]);

        return redirect()->route('models.index')
            ->with('success', 'Paket Model AI berhasil diekstrak dan diaktifkan!');
    }

    public function toggleStatus(ModelAI $model)
    {
        if ($model->status === 'inactive') {
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
        // Hapus gambar grafiknya juga jika ada
        if (!empty($model->metrics_images)) {
            foreach ($model->metrics_images as $pathGambar) {
                Storage::disk('public')->delete($pathGambar);
            }
        }
        $model->delete();
        return back()->with('success', 'Model dan seluruh metriknya berhasil dihapus.');
    }
}