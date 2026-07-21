<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeDocumentEditTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_see_document_inputs_on_employee_edit_form(): void
    {
        $admin = $this->createAdmin('document-form');
        $employee = $this->createEmployee();

        $this->actingAs($admin)
            ->get(route('employee.edit', $employee))
            ->assertOk()
            ->assertSee('Foto dan Dokumen Karyawan')
            ->assertSee('CV &amp; Surat Lamaran', false)
            ->assertSee('name="file_ktp"', false)
            ->assertSee('name="file_cv_lamaran"', false);
    }

    public function test_admin_can_replace_employee_documents_safely(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin('document-update');
        $employee = $this->createEmployee();

        $oldPaths = [
            'dokumen_ktp' => 'employee_documents/old/ktp.jpg',
            'dokumen_kk' => 'employee_documents/old/kk.jpg',
            'dokumen_ijazah' => 'employee_documents/old/ijazah.pdf',
            'dokumen_cv' => 'employee_documents/old/cv.pdf',
        ];

        foreach ($oldPaths as $path) {
            Storage::disk('local')->put($path, 'old-document');
        }
        $employee->update($oldPaths);

        $this->actingAs($admin)
            ->put(route('employee.update', $employee), [
                'nik' => $employee->nik,
                'nama_ktp' => 'Karyawan Dokumen',
                'nama_ibu_kandung' => 'Ibu Karyawan',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1995-01-01',
                'status' => 'Training',
                'file_ktp' => UploadedFile::fake()->image('ktp-baru.jpg', 1200, 800),
                'file_kk' => UploadedFile::fake()->image('kk-baru.png', 1200, 800),
                'file_ijazah' => UploadedFile::fake()->create('ijazah-baru.pdf', 100, 'application/pdf'),
                'file_cv_lamaran' => UploadedFile::fake()->create('cv-lamaran-baru.doc', 100, 'application/msword'),
            ])
            ->assertRedirect(route('employee.index'));

        $employee->refresh();

        $this->assertSame('Ibu Karyawan', $employee->nama_ibu_kandung);
        foreach (array_keys($oldPaths) as $column) {
            $this->assertNotSame($oldPaths[$column], $employee->{$column});
            Storage::disk('local')->assertExists($employee->{$column});
            Storage::disk('local')->assertMissing($oldPaths[$column]);
        }
    }

    private function createAdmin(string $suffix): User
    {
        return User::create([
            'name' => 'Admin Dokumen',
            'email' => "admin-{$suffix}@example.com",
            'password' => 'password',
            'role' => 'Super Admin',
        ]);
    }

    private function createEmployee(): Employee
    {
        return Employee::create([
            'nik' => '3174010101010088',
            'nik_ktp' => '3174010101010088',
            'nama_ktp' => 'Karyawan Dokumen',
            'nama_lengkap' => 'Karyawan Dokumen',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1995-01-01',
            'status' => 'Training',
        ]);
    }
}
