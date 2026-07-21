<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicEmployeeRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_registration_form_is_accessible_without_login(): void
    {
        $this->get(route('public.employee-registration'))
            ->assertOk()
            ->assertSee('Form Pendaftaran Karyawan Baru')
            ->assertSee('Kirim Pendaftaran');
    }

    public function test_new_candidate_registration_creates_training_employee(): void
    {
        $response = $this->post(route('public.employee-registration.store'), [
            'nik' => '3174010101010099',
            'nama_ktp' => 'Kandidat Pendaftaran',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1998-04-04',
            'jenis_kelamin' => 'Pria',
            'alamat' => 'Jakarta Selatan',
            'no_hp' => '081234567899',
            'email' => 'pendaftaran@example.com',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('public.employee-registration.success'));

        $employee = Employee::findOrFail('3174010101010099');
        $this->assertSame('Kandidat Pendaftaran', $employee->nama_lengkap);
        $this->assertSame('Training', $employee->status);
    }
}
