<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CandidateRegistration;
use App\Services\CandidateDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CandidateCompletenessController extends Controller
{
    public function __construct(private readonly CandidateDocumentService $documents)
    {
    }

    public function show()
    {
        return view('public.candidate-completeness');
    }

    public function store(Request $request)
    {
        if (trim((string) $request->input('website')) !== '') {
            abort(422);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'nik_ktp' => ['required', 'digits:16'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
            'mother_name' => ['required', 'string', 'max:150'],
            'work_location' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:150'],
            'ktp' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:15360'],
            'kk' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:15360'],
            'diploma' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:15360'],
            'cv_application' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:15360'],
            'personal_account_confirmation' => ['accepted'],
            'consent' => ['accepted'],
        ], [
            'nik_ktp.digits' => 'NIK KTP harus terdiri dari 16 angka.',
            'personal_account_confirmation.accepted' => 'Rekening wajib merupakan rekening pribadi kandidat.',
            'consent.accepted' => 'Persetujuan penggunaan data harus dicentang.',
            'required' => 'Field ini harus diisi.',
            'max' => 'Ukuran file sebelum optimasi maksimal 15 MB.',
        ]);

        foreach ([
            'full_name',
            'email',
            'birth_place',
            'mother_name',
            'work_location',
            'bank_name',
            'bank_account_holder',
        ] as $field) {
            $validated[$field] = mb_strtoupper(trim((string) $validated[$field]), 'UTF-8');
        }

        $validated['phone'] = trim((string) $validated['phone']);
        $validated['bank_account_number'] = trim((string) $validated['bank_account_number']);

        $candidateRegistration = CandidateRegistration::create([
            'token' => Str::random(64),
            'candidate_name' => $validated['full_name'],
            'status' => 'pending',
            'full_name' => $validated['full_name'],
            'nik_ktp' => $validated['nik_ktp'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'birth_place' => $validated['birth_place'],
            'birth_date' => $validated['birth_date'],
            'mother_name' => $validated['mother_name'],
            'work_location' => $validated['work_location'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_holder' => $validated['bank_account_holder'],
        ]);

        $documents = [
            'ktp' => 'ktp_path',
            'kk' => 'kk_path',
            'diploma' => 'diploma_path',
            'cv_application' => 'cv_path',
        ];

        try {
            foreach ($documents as $input => $column) {
                $validated[$column] = $this->documents->store(
                    $request->file($input),
                    'candidate_registrations/' . $candidateRegistration->id,
                    $input
                );
                unset($validated[$input]);
            }

            unset($validated['personal_account_confirmation']);
            unset($validated['consent']);
            $validated['status'] = 'submitted';
            $validated['submitted_at'] = now();
            $candidateRegistration->update($validated);
        } catch (\Throwable $exception) {
            Storage::disk('local')->deleteDirectory('candidate_registrations/' . $candidateRegistration->id);
            $candidateRegistration->delete();

            throw $exception;
        }

        ActivityLog::log(
            'update',
            'CandidateRegistration',
            (string) $candidateRegistration->id,
            'Kandidat melengkapi data melalui form publik: ' . $candidateRegistration->full_name,
            null,
            ['status' => 'submitted']
        );

        return redirect()->route('public.candidate-registration.success');
    }

    public function success()
    {
        return view('public.candidate-completeness-success');
    }
}
