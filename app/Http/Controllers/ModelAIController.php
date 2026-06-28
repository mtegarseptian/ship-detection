<?php

namespace App\Http\Controllers;

use App\Models\ModelAI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml; // TAMBAHAN: Untuk parsing YAML
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

        // 2. Ekstrak data dari args.yaml SECARA UTUH
        $argsDataUtama = ['model' => null, 'epochs' => null, 'batch' => null, 'imgsz' => null];
        $argsYamlFull = []; // Array untuk menampung seluruh isi YAML
        
        $yamlFiles = File::glob($extractPath . '/**/args.yaml');
        if (!empty($yamlFiles)) {
            // Parsing seluruh isi file YAML menggunakan fungsi bawaan Symfony (tersedia di Laravel)
            try {
                $argsYamlFull = Yaml::parseFile($yamlFiles[0]);
                
                // Ambil data utama untuk diletakkan di luar (kolom terpisah)
                $argsDataUtama['model']  = $argsYamlFull['model'] ?? null;
                $argsDataUtama['epochs'] = $argsYamlFull['epochs'] ?? null;
                $argsDataUtama['batch']  = $argsYamlFull['batch'] ?? null;
                $argsDataUtama['imgsz']  = $argsYamlFull['imgsz'] ?? null;
            } catch (\Exception $e) {
                // Fallback jika parsing Yaml gagal (meski jarang terjadi)
                $yamlContent = file_get_contents($yamlFiles[0]);
                preg_match('/model:\s*(.*)/', $yamlContent, $m); $argsDataUtama['model'] = trim($m[1] ?? '-');
                preg_match('/epochs:\s*([0-9]+)/', $yamlContent, $m); $argsDataUtama['epochs'] = $m[1] ?? null;
                preg_match('/batch:\s*([0-9]+)/', $yamlContent, $m); $argsDataUtama['batch'] = $m[1] ?? null;
                preg_match('/imgsz:\s*([0-9]+)/', $yamlContent, $m); $argsDataUtama['imgsz'] = $m[1] ?? null;
            }
        }

        // 3. Ekstrak data akurasi dari results.csv (Ambil baris terakhir)
        $csvData = ['precision' => 0, 'recall' => 0, 'map50' => 0, 'map50_95' => 0];
        $csvFiles = File::glob($extractPath . '/**/results.csv');
        if (!empty($csvFiles)) {
            $lines = file($csvFiles[0], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lastLine = str_getcsv(array_pop($lines)); 
            if (count($lastLine) >= 7) {
                $csvData['precision'] = (float)($lastLine[4] ?? 0);
                $csvData['recall']    = (float)($lastLine[5] ?? 0);
                $csvData['map50']     = (float)($lastLine[6] ?? 0);
                $csvData['map50_95']  = (float)($lastLine[7] ?? 0);
            }
        }

        // 4. Ambil Gambar (Pisahkan Grafik Evaluasi vs Batch Result)
        $grafikDisimpan = [];
        $batchDisimpan = [];
        
        $grafikTarget = [
            'confusion_matrix.png', 'confusion_matrix_normalized.png',
            'F1_curve.png', 'P_curve.png', 'PR_curve.png', 'R_curve.png',
            'BoxF1_curve.png', 'BoxP_curve.png', 'BoxPR_curve.png', 'BoxR_curve.png',
            'results.png', 'labels.jpg', 'labels_correlogram.jpg'
        ];
        
        $semuaGambar = File::allFiles($extractPath);
        $folderMetrics = 'models/metrics/' . uniqid() . '/';
        
        foreach ($semuaGambar as $file) {
            $namaFile = $file->getFilename();
            $ext = strtolower($file->getExtension());
            
            // Cek jika ini adalah file gambar
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $pathTujuan = $folderMetrics . $namaFile;
                
                // Jika nama file mengandung kata 'batch' (contoh: val_batch0_labels.jpg)
                if (str_contains(strtolower($namaFile), 'batch')) {
                    Storage::disk('public')->put($pathTujuan, file_get_contents($file->getRealPath()));
                    $batchDisimpan[$namaFile] = $pathTujuan;
                } 
                // Jika nama file ada di list grafik target
                elseif (in_array($namaFile, $grafikTarget)) {
                    Storage::disk('public')->put($pathTujuan, file_get_contents($file->getRealPath()));
                    $grafikDisimpan[$namaFile] = $pathTujuan;
                }
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
            'status'         => 'active',
            'uploaded_by'    => Auth::id(),
            
            // Konfigurasi YAML Utama
            'base_model'     => $argsDataUtama['model'],
            'epochs'         => $argsDataUtama['epochs'],
            'batch_size'     => $argsDataUtama['batch'],
            'imgsz'          => $argsDataUtama['imgsz'],
            
            // Konfigurasi YAML Penuh (JSON)
            'args_yaml'      => $argsYamlFull,
            
            // Metrik CSV
            'precision'      => $csvData['precision'],
            'recall'         => $csvData['recall'],
            'map50'          => $csvData['map50'],
            'map50_95'       => $csvData['map50_95'],
            
            // File Gambar (Dipisah menjadi 2 kategori)
            'metrics_images' => $grafikDisimpan,
            'batch_images'   => $batchDisimpan,
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
        
        // Hapus gambar grafiknya
        if (!empty($model->metrics_images)) {
            foreach ($model->metrics_images as $pathGambar) {
                Storage::disk('public')->delete($pathGambar);
            }
        }
        
        // Hapus gambar batchnya
        if (!empty($model->batch_images)) {
            foreach ($model->batch_images as $pathBatch) {
                Storage::disk('public')->delete($pathBatch);
            }
        }
        
        $model->delete();
        return back()->with('success', 'Model dan seluruh metriknya berhasil dihapus.');
    }
}