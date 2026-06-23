<?php

namespace App\Http\Controllers;

use App\Models\Detection;
use App\Models\ModelAI;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 💡 PERBAIKAN: Tambahkan query() setelah Model
        $totalDetections = Detection::query()->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))->count();
        $doneDetections  = Detection::query()->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))->where('status', 'done')->count();
        $totalShips      = Detection::query()->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))->sum('ship_count');
        $totalModels     = ModelAI::query()->where('status', 'active')->count();
        $totalUsers      = $user->isAdmin() ? User::query()->count() : null;

        // Data grafik: deteksi per hari 7 hari terakhir
        // 💡 PERBAIKAN: Tambahkan query() setelah Model
        $chartData = Detection::query()->selectRaw('DATE(created_at) as date, COUNT(*) as total, SUM(ship_count) as ships')
            ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 💡 PERBAIKAN: Tambahkan query() setelah Model
        $recentDetections = Detection::query()->with(['user', 'modelAI'])
            ->when(!$user->isAdmin(), fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalDetections', 'doneDetections', 'totalShips',
            'totalModels', 'totalUsers', 'chartData', 'recentDetections'
        ));
    }
}