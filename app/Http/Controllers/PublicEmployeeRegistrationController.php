<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicEmployeeRegistrationController extends Controller
{
    public function create()
    {
        $positions = config('positions.operational_positions', []);

        return view('public.employee-registration', compact('positions'));
    }

    public function store(Request $request)
    {
        if (trim((string) $request->input('website')) !== '') {
            abort(422);
        }

        $positions = config('positions.operational_positions', []);
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', 'unique:employees,nik'],
            'nama_ktp' => ['required', 'string', 'max:100'],
            'nama_ibu_kandung' => ['required', 'string', 'max:150'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', Rule::in(['Pria', 'Wanita'])],
            'agama' => ['required', Rule::in(['Islam', 'Kristen', 'Katholik', 'Budha', 'Hindu', 'Konghucu'])],
            'status_pernikahan' => ['nullable', Rule::in(['Single', 'Menikah', 'Duda', 'Janda'])],
            'jumlah_anak' => ['nullable', 'required_if:status_pernikahan,Menikah,Duda,Janda', 'integer', 'min:0', 'max:50'],
            'alamat' => ['required', 'string', 'max:255'],
            'kelurahan' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'propinsi' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100'],
            'no_kk' => ['required', 'digits:16'],
            'ec_nama' => ['nullable', 'string', 'max:100'],
            'ec_no_hp' => ['nullable', 'string', 'max:20'],
            'ec_hubungan' => ['nullable', 'string', 'max:50'],
            'nama_bank' => ['required', 'string', 'max:50'],
            'no_rekening' => ['required', 'string', 'max:30'],
            'nama_di_rekening' => ['required', 'string', 'max:100'],
            'posisi' => ['nullable', Rule::in($positions)],
            'nama_customer' => ['nullable', 'string', 'max:150'],
            'tanggal_masuk' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:10240'],
            'consent' => ['accepted'],
        ], [
            'nik.unique' => 'NIK KTP sudah terdaftar. Hubungi admin jika Anda pernah mengisi data.',
            'nik.digits' => 'NIK KTP harus terdiri dari 16 angka.',
            'no_kk.digits' => 'No. KK harus terdiri dari 16 angka.',
            'jumlah_anak.required_if' => 'Jumlah anak wajib diisi untuk status Menikah, Duda, atau Janda.',
            'consent.accepted' => 'Persetujuan penggunaan data harus dicentang.',
            'required' => 'Field ini harus diisi.',
        ]);

        unset($validated['consent']);
        $validated['nik_ktp'] = $validated['nik'];
        $validated['nama_lengkap'] = $validated['nama_ktp'];
        $validated['status'] = 'Training';
        $validated['no_pks_masuk'] = null;
        $validated['posisi'] = isset($validated['posisi']) ? strtoupper($validated['posisi']) : null;
        if (!in_array($validated['status_pernikahan'] ?? null, ['Menikah', 'Duda', 'Janda'], true)) {
            $validated['jumlah_anak'] = null;
        }

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('employee_photos', 'public');
        }

        $employee = Employee::create($validated);

        ActivityLog::log(
            'create',
            'Employee',
            $employee->nik,
            'Pendaftaran calon karyawan melalui form publik: ' . $employee->nama_lengkap,
            null,
            $validated
        );

        return redirect()->route('public.employee-registration.success');
    }

    public function success()
    {
        return view('public.employee-registration-success');
    }
}
