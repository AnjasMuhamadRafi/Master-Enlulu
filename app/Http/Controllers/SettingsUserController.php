<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsUserController extends Controller
{
    public function index()
    {
        $users = User::orderByDesc('created_at')->get();
        $availablePositions = $this->availableManagedPositions();

        return view('settings.users', compact('users', 'availablePositions'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'Super Admin') {
            abort(403, 'Hanya Super Admin yang dapat menambah user baru.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:100|unique:users,email',
            'role'     => ['required', Rule::in(['Admin', 'Staff', 'Super Admin'])],
            'handled_position' => 'nullable|array',
            'handled_position.*' => 'nullable|string|max:120',
            'handled_position_manual' => 'nullable|string|max:1000',
            'password' => 'required|string|min:8|max:100',
        ], [
            'name.required'     => 'Nama wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'role.required'     => 'Role wajib dipilih.',
            'role.in'           => 'Role tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        // Kosongkan handled_position jika bukan Admin
        if ($validated['role'] !== 'Admin') {
            $validated['handled_position'] = null;
        } else {
            $validated['handled_position'] = $this->normalizeHandledPositions(
                $request->input('handled_position', []),
                $request->input('handled_position_manual')
            );
        }

        unset($validated['handled_position_manual']);
        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);
        
        // Log aktivitas - jangan log password
        $logData = $validated;
        unset($logData['password']);
        
        ActivityLog::log(
            'create',
            'User',
            $user->id,
            'User baru ditambahkan: ' . $user->name . ' (' . $user->email . ')',
            null,
            $logData
        );

        return redirect()->route('settings.users')
            ->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Show form for editing user
     */
    public function edit(User $user)
    {
        if (auth()->user()->role !== 'Super Admin') {
            abort(403, 'Hanya Super Admin yang dapat mengedit user.');
        }

        return view('settings.users-edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        if (auth()->user()->role !== 'Super Admin') {
            abort(403, 'Hanya Super Admin yang dapat mengubah user.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:100|unique:users,email,' . $user->id,
            'role'     => ['required', Rule::in(['Admin', 'Staff', 'Super Admin'])],
            'handled_position' => 'nullable|array',
            'handled_position.*' => 'nullable|string|max:120',
            'handled_position_manual' => 'nullable|string|max:1000',
        ], [
            'name.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah terdaftar.',
            'role.required'  => 'Role wajib dipilih.',
            'role.in'        => 'Role tidak valid.',
        ]);

        // Kosongkan handled_position jika bukan Admin
        if ($validated['role'] !== 'Admin') {
            $validated['handled_position'] = null;
        } else {
            $validated['handled_position'] = $this->normalizeHandledPositions(
                $request->input('handled_position', []),
                $request->input('handled_position_manual')
            );
        }

        unset($validated['handled_position_manual']);
        $oldValues = $user->getAttributes();
        $user->update($validated);

        ActivityLog::log(
            'update',
            'User',
            $user->id,
            'Data user diperbarui: ' . $user->name,
            $oldValues,
            $validated
        );

        return redirect()->route('settings.users')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        if (auth()->user()->role !== 'Super Admin') {
            abort(403, 'Hanya Super Admin yang dapat menghapus user.');
        }

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->route('settings.users')
                ->with('error', 'Anda tidak dapat menghapus user yang sedang login.');
        }

        $userData = $user->getAttributes();
        $user->delete();

        ActivityLog::log(
            'delete',
            'User',
            $user->id,
            'User dihapus: ' . $user->name,
            $userData,
            null
        );

        return redirect()->route('settings.users')
            ->with('success', 'User berhasil dihapus.');
    }

    public function updateSecurity(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'old_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'old_password.required' => 'Password lama wajib diisi.',
            'old_password.current_password' => 'Password lama tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $user->update([
            'password' => bcrypt($validated['password']),
        ]);

        ActivityLog::log(
            'update',
            'User',
            $user->id,
            'Password diubah oleh user',
            null,
            ['changed_field' => 'password']
        );

        return redirect()->route('settings.security')
            ->with('success', 'Password berhasil diubah.');
    }

    /**
     * Posisi yang bisa dipilih ADMIN/PIC diambil dari data karyawan dan config lama.
     */
    private function availableManagedPositions(): array
    {
        return collect(config('positions.operational_positions', []))
            ->merge(
                collect(config('positions.admin_pic_departments', []))
                    ->flatten()
            )
            ->merge(
                Employee::query()
                    ->whereNotNull('posisi')
                    ->distinct()
                    ->pluck('posisi')
            )
            ->map(fn ($position) => $this->normalizePosition($position))
            ->filter(fn ($position) => $position !== '' && !str_starts_with($position, 'ADMIN/PIC-'))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Gabungkan checkbox dan input manual menjadi daftar posisi aktual.
     */
    private function normalizeHandledPositions(array $selectedPositions, ?string $manualPositions): array
    {
        $manual = preg_split('/[\r\n,]+/', (string) $manualPositions) ?: [];

        return collect($selectedPositions)
            ->merge($manual)
            ->map(fn ($position) => $this->normalizePosition($position))
            ->filter(fn ($position) => $position !== '' && !str_starts_with($position, 'ADMIN/PIC-'))
            ->unique()
            ->map(fn ($position) => 'position:' . $position)
            ->values()
            ->all();
    }

    private function normalizePosition(?string $position): string
    {
        return trim(strtoupper((string) $position));
    }
}
