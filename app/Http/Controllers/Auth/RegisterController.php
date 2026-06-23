<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    // Menampilkan form pendaftaran
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Memproses data pendaftaran
    public function register(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique'       => 'Email ini sudah terdaftar.',
            'password.min'       => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        // 2. Simpan ke Database
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            // Hash password untuk keamanan
            'password' => Hash::make($request->password),
            // Opsional: Jika tabel Anda punya kolom role, set default ke 'user' atau 'operator'
            // 'role'  => 'user', 
        ]);

        // 3. Login otomatis setelah mendaftar
        Auth::login($user);

        // 4. Arahkan ke Dashboard
        return redirect()->route('dashboard')
            ->with('success', 'Pendaftaran berhasil! Selamat datang di ShipDetect AI.');
    }
}