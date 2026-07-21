<?php

namespace Tests\Feature;

use App\Models\CandidateRegistration;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CandidateCompletenessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_universal_registration_form_is_accessible_without_login(): void
    {
        $this->get(route('public.candidate-registration.show'))
            ->assertOk()
            ->assertSee('Form Kelengkapan Data Karyawan')
            ->assertSee('Kirim Data')
            ->assertSee('Foto KTP')
            ->assertSee('Nama Ibu Kandung');
    }

    public function test_each_submission_creates_a_new_candidate_record(): void
    {
        Storage::fake('local');

        $this->post(route('public.candidate-registration.store'), $this->validSubmissionData())
            ->assertRedirect(route('public.candidate-registration.success'));

        $this->assertDatabaseHas('candidate_registrations', [
            'full_name' => 'KANDIDAT LENGKAP',
            'nik_ktp' => '3174010101010001',
            'status' => 'submitted',
        ]);
    }

    public function test_candidate_can_submit_required_data_and_documents(): void
    {
        Storage::fake('local');

        $response = $this->post(route('public.candidate-registration.store'), $this->validSubmissionData());
        $response->assertRedirect(route('public.candidate-registration.success'));

        $registration = CandidateRegistration::latest('id')->firstOrFail();
        $this->assertSame('submitted', $registration->status);
        $this->assertNotNull($registration->submitted_at);
        Storage::disk('local')->assertExists($registration->ktp_path);
        Storage::disk('local')->assertExists($registration->kk_path);
        Storage::disk('local')->assertExists($registration->diploma_path);
        Storage::disk('local')->assertExists($registration->cv_path);
        $this->assertStringEndsWith('.jpg', $registration->ktp_path);
        $this->assertLessThanOrEqual(450 * 1024, Storage::disk('local')->size($registration->ktp_path));
        $this->assertSame('KANDIDAT LENGKAP', $registration->full_name);
        $this->assertSame('NAMA IBU', $registration->mother_name);
    }

    public function test_admin_can_apply_candidate_data_and_documents_to_employee_master(): void
    {
        Storage::fake('local');

        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-candidate-test@example.com',
            'password' => 'password',
            'role' => 'Super Admin',
        ]);

        $employee = Employee::create([
            'nik' => '3174010101010002',
            'nama_lengkap' => 'Nama Lama',
            'status' => 'Training',
        ]);

        $documentPaths = [
            'ktp_path' => 'candidate_registrations/1/ktp.jpg',
            'kk_path' => 'candidate_registrations/1/kk.jpg',
            'diploma_path' => 'candidate_registrations/1/ijazah.pdf',
            'cv_path' => 'candidate_registrations/1/cv.pdf',
        ];

        foreach ($documentPaths as $path) {
            Storage::disk('local')->put($path, 'test-document');
        }

        $registration = CandidateRegistration::create([
            'token' => str_repeat('a', 64),
            'candidate_name' => 'Nama Baru',
            'employee_nik' => $employee->nik,
            'status' => 'submitted',
            'submitted_at' => now(),
            'full_name' => 'Nama Baru',
            'nik_ktp' => $employee->nik,
            'phone' => '081234567891',
            'email' => 'nama-baru@example.com',
            'birth_place' => 'Bandung',
            'birth_date' => '1996-02-02',
            'mother_name' => 'Ibu Kandidat',
            'work_location' => 'Bandung',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '9876543210',
            'bank_account_holder' => 'Nama Baru',
        ] + $documentPaths);

        $this->actingAs($admin)
            ->post(route('candidate-registration.apply', $registration))
            ->assertRedirect();

        $employee->refresh();
        $registration->refresh();

        $this->assertSame('Nama Baru', $employee->nama_lengkap);
        $this->assertSame('Ibu Kandidat', $employee->nama_ibu_kandung);
        $this->assertNotNull($employee->dokumen_ktp);
        $this->assertNotNull($registration->applied_at);
        Storage::disk('local')->assertExists($employee->dokumen_ktp);
        Storage::disk('local')->assertExists($employee->dokumen_cv);
    }

    public function test_admin_can_export_candidate_completeness_to_excel(): void
    {
        $admin = User::create([
            'name' => 'Admin Export',
            'email' => 'admin-export-candidate@example.com',
            'password' => 'password',
            'role' => 'Super Admin',
        ]);

        CandidateRegistration::create([
            'token' => str_repeat('b', 64),
            'candidate_name' => 'KANDIDAT EXPORT',
            'status' => 'submitted',
            'submitted_at' => now(),
            'full_name' => 'KANDIDAT EXPORT',
            'nik_ktp' => '3174010101010003',
            'phone' => '081234567892',
            'email' => 'KANDIDAT@EXAMPLE.COM',
            'birth_place' => 'JAKARTA',
            'birth_date' => '1997-03-03',
            'mother_name' => 'IBU EXPORT',
            'work_location' => 'JAKARTA',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567891',
            'bank_account_holder' => 'KANDIDAT EXPORT',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('candidate-registration.export'));

        $response->assertOk();
        $response->assertDownload();
        $this->assertStringStartsWith('PK', $response->streamedContent());
    }

    private function validSubmissionData(): array
    {
        return [
            'full_name' => 'Kandidat Lengkap',
            'nik_ktp' => '3174010101010001',
            'phone' => '081234567890',
            'email' => 'kandidat@example.com',
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-01-01',
            'mother_name' => 'Nama Ibu',
            'work_location' => 'Jakarta Selatan',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Kandidat Lengkap',
            'ktp' => UploadedFile::fake()->image('ktp.jpg'),
            'kk' => UploadedFile::fake()->image('kk.jpg'),
            'diploma' => UploadedFile::fake()->create('ijazah.pdf', 100, 'application/pdf'),
            'cv_application' => UploadedFile::fake()->create('cv-lamaran.pdf', 100, 'application/pdf'),
            'personal_account_confirmation' => '1',
            'consent' => '1',
        ];
    }
}
