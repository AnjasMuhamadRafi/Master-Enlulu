<?php

namespace Tests\Unit;

use App\Models\Contract;
use App\Models\Employee;
use App\Services\ContractDocumentService;
use Carbon\Carbon;
use Tests\TestCase;
use ZipArchive;

class ContractDocumentServiceTest extends TestCase
{
    public function test_it_replaces_contract_mail_merge_fields(): void
    {
        $employee = new Employee([
            'nik' => '3174010101010001',
            'nik_ktp' => '3174010101010001',
            'nik_enlulu' => 'ENL-001',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'Pria',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => Carbon::parse('1995-08-17'),
            'alamat' => 'Jalan Merdeka 10',
            'posisi' => 'Sprinter',
            'nama_customer' => 'PT Pelanggan Utama',
        ]);
        $contract = new Contract([
            'contract_number' => '6000/HR-ESM/V/26',
            'contract_date' => Carbon::parse('2026-05-25'),
            'start_date' => Carbon::parse('2026-05-25'),
            'end_date' => Carbon::parse('2027-05-24'),
        ]);
        $contract->setRelation('employee', $employee);

        $path = (new ContractDocumentService())->createDocument($contract);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $templateZip = new ZipArchive();
        $this->assertTrue($templateZip->open(storage_path('app/contracts/templates/perjanjian-mitra-2025.docx')) === true);
        $templateXml = $templateZip->getFromName('word/document.xml');
        $templateZip->close();
        @unlink($path);

        $this->assertStringContainsString('MERGEFIELD', $xml);
        $this->assertStringContainsString('Budi Santoso', $xml);
        $this->assertStringContainsString('25 Mei 2026', $xml);
        $this->assertStringContainsString('PT Pelanggan Utama', $xml);
        $this->assertStringContainsString('ENL-001', $xml);
        $normalizedTemplate = preg_replace('~(<w:t[^>]*>).*?(</w:t>)~s', '$1__TEXT__$2', $templateXml);
        $normalizedOutput = preg_replace('~(<w:t[^>]*>).*?(</w:t>)~s', '$1__TEXT__$2', $xml);
        $this->assertSame(hash('sha256', $normalizedTemplate), hash('sha256', $normalizedOutput));
    }
}
