<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna dengan filter dan pencarian
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        // Filter pencarian nama / email / NIP
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Filter Role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        // Filter Status Aktif
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') == '1');
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::orderBy('display_name', 'asc')->get();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Menyimpan pengguna baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'nip' => ['nullable', 'string', 'max:50', 'unique:users,nip'],
            'phone' => ['nullable', 'string', 'max:25'],
            'position' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'role_id.required' => 'Role pengguna wajib dipilih.',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'username' => $request->input('username') ?: null,
            'password' => Hash::make($request->input('password')),
            'role_id' => $request->input('role_id'),
            'nip' => $request->input('nip'),
            'phone' => $request->input('phone'),
            'position' => $request->input('position'),
            'department' => $request->input('department'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Pengguna ' . $user->name . ' berhasil ditambahkan.',
                'data' => $user,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Mengambil detail pengguna untuk modal edit (JSON via jQuery AJAX)
     */
    public function edit(User $user)
    {
        return response()->json([
            'status' => 'success',
            'data' => $user->load('role'),
        ]);
    }

    /**
     * Memperbarui data pengguna
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nip')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'position' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'role_id.required' => 'Role pengguna wajib dipilih.',
        ]);

        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'username' => $request->input('username') ?: null,
            'role_id' => $request->input('role_id'),
            'nip' => $request->input('nip'),
            'phone' => $request->input('phone'),
            'position' => $request->input('position'),
            'department' => $request->input('department'),
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengguna berhasil diperbarui.',
                'data' => $user,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus akun pengguna
     */
    public function destroy(Request $request, User $user)
    {
        // Mencegah pengguna menghapus akunnya sendiri
        if (Auth::id() === $user->id) {
            $msg = 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.';
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return redirect()->route('users.index')->with('error', $msg);
        }

        $userName = $user->name;
        $user->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Pengguna ' . $userName . ' berhasil dihapus.',
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Toggle status aktif pengguna via jQuery AJAX
     */
    public function toggleActive(Request $request, User $user)
    {
        if (Auth::id() === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
            ], 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'status' => 'success',
            'is_active' => $user->is_active,
            'message' => 'Status pengguna ' . $user->name . ' berhasil diubah menjadi ' . ($user->is_active ? 'Aktif' : 'Non-Aktif') . '.',
        ]);
    }

    /**
     * Export daftar pengguna ke CSV
     */
    public function export(Request $request)
    {
        $users = User::with('role')->orderBy('name', 'asc')->get();

        $filename = 'daftar_pengguna_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            // Tambahkan BOM untuk UTF-8 support Excel
            fputs($file, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($file, ['ID', 'NIP', 'Nama', 'Username', 'Email', 'Role', 'Jabatan', 'Divisi', 'No. HP', 'Status']);

            foreach ($users as $u) {
                fputcsv($file, [
                    $u->id,
                    $u->nip ?? '-',
                    $u->name,
                    $u->username ?? '-',
                    $u->email,
                    $u->role?->display_name ?? 'Tanpa Role',
                    $u->position ?? '-',
                    $u->department ?? '-',
                    $u->phone ?? '-',
                    $u->is_active ? 'Aktif' : 'Non-Aktif',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
