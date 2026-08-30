<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman formulir login
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Memproses percobaan login pengguna
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'], // Bisa berupa email atau username
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Email atau Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginInput = $request->input('login');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Tentukan apakah input berupa email atau username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Cek kredensial
        if (Auth::attempt([$fieldType => $loginInput, 'password' => $password], $remember)) {
            $user = Auth::user();

            // Cek apakah akun aktif
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'login' => 'Akun Anda berstatus non-aktif. Silakan hubungi administrator.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        throw ValidationException::withMessages([
            'login' => 'Kombinasi email/username dan kata sandi tidak cocok.',
        ]);
    }

    /**
     * Quick Demo Login untuk mempermudah pengujian hak akses
     */
    public function demoLogin(string $role)
    {
        $emailMap = [
            'superadmin' => 'admin@absensi.com',
            'admin' => 'admin2@absensi.com',
            'user' => 'user@absensi.com',
        ];

        if (!array_key_exists($role, $emailMap)) {
            return redirect()->route('login')->with('error', 'Role demo tidak valid.');
        }

        $user = User::where('email', $emailMap[$role])->first();

        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->route('dashboard')
                ->with('success', 'Login berhasil sebagai ' . ($user->role?->display_name ?? 'User') . ' (Demo)!');
        }

        return redirect()->route('login')->with('error', 'Akun demo tidak ditemukan.');
    }

    /**
     * Memproses logout pengguna
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
