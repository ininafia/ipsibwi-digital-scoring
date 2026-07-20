<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AkunController extends Controller
{
    /**
     * Menampilkan daftar semua akun dari tabel users.
     */
    public function index(): View
    {
        $users = DB::table('users')
            ->select('id', 'username', 'access_type', 'is_active', 'created_at')
            ->orderBy('access_type', 'asc')
            ->orderBy('username', 'asc')
            ->get();

        // Mapping nama role agar lebih mudah dibaca
        $roleMap = [
            1 => 'Operator',
            2 => 'Ketua Pertandingan',
            3 => 'Dewan',
            4 => 'Timer',
            5 => 'Juri',
            6 => 'Wasit',
            7 => 'Delegasi Teknik'
        ];

        foreach ($users as $user) {
            $user->role_name = $roleMap[$user->access_type] ?? 'Unknown Role (' . $user->access_type . ')';
        }

        return view('Operator.akun.index', compact('users'));
    }

    /**
     * Mereset password akun ke nilai yang baru dimasukkan oleh Operator.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'new_password' => 'required|string|min:4'
        ]);

        try {
            DB::table('users')
                ->where('id', $request->user_id)
                ->update([
                    'password' => Hash::make($request->new_password),
                    'updated_at' => now()
                ]);

            return redirect()->back()->with('success', 'Berhasil mereset password akun.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mereset password: ' . $e->getMessage());
        }
    }
}
