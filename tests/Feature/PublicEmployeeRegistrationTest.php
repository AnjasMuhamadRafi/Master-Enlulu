<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicEmployeeRegistrationTest extends TestCase
{
    public function test_registration_form_is_accessible_without_login(): void
    {
        $this->get('/pendaftaran-karyawan')
            ->assertOk()
            ->assertSee('Form Data Calon Karyawan')
            ->assertSee('Kirim Data');
    }
}
