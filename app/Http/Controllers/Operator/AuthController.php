<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * --------------------------------------------------------------------------
     * HALAMAN LOGIN
     * --------------------------------------------------------------------------
     */
    public function login(): View
    {
        return view('Operator.Auth.login');
    }

    /**
     * --------------------------------------------------------------------------
     * PROSES LOGIN
     * --------------------------------------------------------------------------
     */
    public function doLogin(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI INPUT
        |--------------------------------------------------------------------------
        */
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA USER
        |--------------------------------------------------------------------------
        */
        $user = DB::table('users')
            ->where('username', $credentials['username'])
            ->where('is_active', 1)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | VALIDASI KREDENSIAL (USER / PASSWORD)
        |--------------------------------------------------------------------------
        */
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors([
                    'login' => 'Maaf username atau password salah'
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | ROLE USER
        |--------------------------------------------------------------------------
        */
        $role = (int) $user->access_type;

        /*
        |--------------------------------------------------------------------------
        | SIMPAN SESSION
        |--------------------------------------------------------------------------
        */
        $sessionData = [
            'user_id'   => $user->id,
            'username'  => $user->username,
            'role'      => $role,
            'login_at'  => now(),
        ];

        // Jika login sebagai juri, simpan posisinya di session
        if ($role === 5) {
            $juriNum = preg_replace('/[^0-9]/', '', $user->username); // juri1 -> 1
            if (in_array($juriNum, ['1', '2', '3'])) {
                $sessionData['juri_position'] = 'juri_' . $juriNum;
            }
        }

        session($sessionData);

        // Regenerate session ID untuk mencegah session fixation
        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | REDIRECT BERDASARKAN ROLE
        |--------------------------------------------------------------------------
        */
        if ($role === 1) {
            return redirect()->route('dashboard')->with('success', 'Selamat datang Operator');
        } elseif ($role === 2) {
            return redirect()->route('ketua.dashboard')->with('success', 'Selamat datang Ketua Pertandingan');
        } elseif ($role === 3) {
            return redirect()->route('dewan.dashboard')->with('success', 'Selamat datang Dewan');
        } elseif ($role === 4) {
            return redirect()->route('timer.dashboard')->with('success', 'Selamat datang Timer');
        } elseif ($role === 5) {
            $uname = strtolower($user->username);
            if (str_contains($uname, 'juri2') || str_contains($uname, 'juri_2')) {
                return redirect()->route('juri2')->with('success', 'Selamat datang Juri 2');
            } elseif (str_contains($uname, 'juri3') || str_contains($uname, 'juri_3')) {
                return redirect()->route('juri3')->with('success', 'Selamat datang Juri 3');
            }
            return redirect()->route('juri1')->with('success', 'Selamat datang Juri 1');
        } elseif ($role === 6) {
            return redirect()->route('login')->with('success', 'Selamat datang Wasit (Dashboard belum tersedia)');
        } elseif ($role === 7) {
            return redirect()->route('login')->with('success', 'Selamat datang Delegasi Teknik (Dashboard belum tersedia)');
        } else {
            return redirect()->route('login')->with('error', 'Role tidak dikenali');
        }
    }

    /**
     * --------------------------------------------------------------------------
     * LOGOUT
     * --------------------------------------------------------------------------
     */
    public function doLogout(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | INVALIDATE SESSION & REGENERATE TOKEN
        |--------------------------------------------------------------------------
        */
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        /*
        |--------------------------------------------------------------------------
        | REDIRECT LOGIN
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->route('login')
            ->with('success', 'Berhasil logout');
    }

    /**
     * --------------------------------------------------------------------------
     * PROFILE
     * --------------------------------------------------------------------------
     */
    public function profile(): View
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA USER LOGIN
        |--------------------------------------------------------------------------
        */
        $user = DB::table('users')
            ->where('id', session('user_id'))
            ->first();

        /*
        |--------------------------------------------------------------------------
        | TAMPILKAN PROFILE
        |--------------------------------------------------------------------------
        */
        return view('Operator.Auth.profile', compact('user'));
    }
}