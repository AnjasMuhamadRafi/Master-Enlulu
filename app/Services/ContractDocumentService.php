<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Str;
use ZipArchive;

class ContractDocumentService
{
    public const TEMPLATE_NAME = 'Perjanjian Mitra 2025';

    public function createDocument(Contract $contract): string
    {
        $template = storage_path('app/contracts/templates/perjanjian-mitra-2025.docx');

        if (!is_file($template)) {
            throw new \RuntimeException('Template kontrak belum tersedia.');
        }

        $output = tempnam(sys_get_temp_dir(), 'contract_');
        copy($template, $output);

        $values = $this->values($contract);
        $zip = new ZipArchive();

        if ($zip->open($output) !== true) {
            @unlink($output);
            throw new \RuntimeException('Dokumen kontrak tidak dapat dibuka.');
        }

        try {
            $names = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $names[] = $zip->getNameIndex($i);
            }

            foreach ($names as $name) {
                if (!preg_match('~^word/(document|header\d+|footer\d+)\.xml$~', $name)) {
                    continue;
                }

                $xml = $zip->getFromName($name);
                $updated = $this->replaceFields($xml, $values);

                if ($updated !== $xml) {
                    $zip->addFromString($name, $updated);
                }
            }
        } finally {
            $zip->close();
        }

        return $output;
    }

    public function downloadName(Contract $contract): string
    {
        $employee = Str::slug($contract->employee?->nama_lengkap ?: $contract->employee_nik);
        $number = Str::slug($contract->contract_number);

        return "Kontrak-{$number}-{$employee}.docx";
    }

    private function values(Contract $contract): array
    {
        $employee = $contract->employee;
        $gender = strtolower(trim((string) $employee?->jenis_kelamin));
        $gender = in_array($gender, ['perempuan', 'wanita', 'female', 'p'], true)
            ? 'Perempuan'
            : 'Laki-laki';

        return [
            'No_Kontrak' => $contract->contract_number,
            'Hari_kontrak' => $this->formatDate($contract->contract_date, 'l'),
            'tgl_kontrak' => $this->formatDate($contract->contract_date, 'j'),
            'Bln_kontrak' => $this->formatDate($contract->contract_date, 'F'),
            'Thn_kontrak' => $this->formatDate($contract->contract_date, 'Y'),
            'Nama_Karyawan' => $employee?->nama_lengkap,
            'Gender' => $gender,
            'Tmp_Lahir' => $employee?->tempat_lahir,
            'Tgl_Lahir' => $this->formatDate($employee?->tanggal_lahir, 'j F Y'),
            'Alamat' => $employee?->alamat,
            'No_KTP' => $employee?->nik_ktp ?: $employee?->nik,
            'NIK_ENLULU' => $employee?->nik_enlulu,
            'Posisi' => $employee?->posisi,
            'Klien' => $employee?->nama_customer,
            'Start_Date_1' => $this->formatDate($contract->start_date, 'j F Y'),
            'End_Date_1' => $this->formatDate($contract->end_date, 'j F Y'),
        ];
    }

    private function formatDate($date, string $format): string
    {
        if (!$date) {
            return '-';
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $days = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
        ];

        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);

        return strtr($date->format($format), [
            'Monday' => $days[1], 'Tuesday' => $days[2], 'Wednesday' => $days[3],
            'Thursday' => $days[4], 'Friday' => $days[5], 'Saturday' => $days[6],
            'Sunday' => $days[0],
            'January' => $months[1], 'February' => $months[2], 'March' => $months[3],
            'April' => $months[4], 'May' => $months[5], 'June' => $months[6],
            'July' => $months[7], 'August' => $months[8], 'September' => $months[9],
            'October' => $months[10], 'November' => $months[11], 'December' => $months[12],
        ]);
    }

    private function replaceFields(string $xml, array $values): string
    {
        preg_match_all(
            '~<w:instrText[^>]*>\s*MERGEFIELD\s+"?([A-Za-z0-9_]+)"?.*?</w:instrText>~s',
            $xml,
            $fields,
            PREG_OFFSET_CAPTURE
        );

        for ($index = count($fields[0]) - 1; $index >= 0; $index--) {
            $field = $fields[1][$index][0];
            $instruction = $fields[0][$index];
            $searchFrom = $instruction[1] + strlen($instruction[0]);
            $separate = strpos($xml, 'w:fldCharType="separate"', $searchFrom);
            $end = $separate === false ? false : strpos($xml, 'w:fldCharType="end"', $separate);
            $textStart = $separate === false ? false : strpos($xml, '<w:t', $separate);

            if ($separate === false || $end === false || $textStart === false || $textStart > $end) {
                continue;
            }

            $textContentStart = strpos($xml, '>', $textStart);
            $textContentEnd = $textContentStart === false ? false : strpos($xml, '</w:t>', $textContentStart);

            if ($textContentStart === false || $textContentEnd === false || $textContentEnd > $end) {
                continue;
            }

            $textContentStart++;
            $value = htmlspecialchars((string) ($values[$field] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $xml = substr_replace(
                $xml,
                $value,
                $textContentStart,
                $textContentEnd - $textContentStart
            );
        }

        return $xml;
    }
}
