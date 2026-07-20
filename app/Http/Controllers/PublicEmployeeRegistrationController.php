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
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', Rule::in(['Pria', 'Wanita'])],
            'agama' => ['nullable', 'string', 'max:30'],
            'status_pernikahan' => ['nullable', 'string', 'max:30'],
            'alamat' => ['required', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kota' => ['nullable', 'string', 'max:100'],
            'propinsi' => ['nullable', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'no_kk' => ['nullable', 'string', 'max:20'],
            'ec_nama' => ['nullable', 'string', 'max:100'],
            'ec_no_hp' => ['nullable', 'string', 'max:20'],
            'ec_hubungan' => ['nullable', 'string', 'max:50'],
            'nama_bank' => ['nullable', 'string', 'max:50'],
            'no_rekening' => ['nullable', 'string', 'max:30'],
            'nama_di_rekening' => ['nullable', 'string', 'max:100'],
            'posisi' => ['nullable', Rule::in($positions)],
            'nama_customer' => ['nullable', 'string', 'max:150'],
            'tanggal_masuk' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:10240'],
            'consent' => ['accepted'],
        ], [
            'nik.unique' => 'NIK KTP sudah terdaftar. Hubungi admin jika Anda pernah mengisi data.',
            'nik.digits' => 'NIK KTP harus terdiri dari 16 angka.',
            'consent.accepted' => 'Persetujuan penggunaan data harus dicentang.',
            'required' => 'Field ini harus diisi.',
        ]);

        unset($validated['consent']);
        $validated['nik_ktp'] = $validated['nik'];
        $validated['nama_lengkap'] = $validated['nama_ktp'];
        $validated['status'] = 'Training';
        $validated['no_pks_masuk'] = null;
        $validated['posisi'] = isset($validated['posisi']) ? strtoupper($validated['posisi']) : null;

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
