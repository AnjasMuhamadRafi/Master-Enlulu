<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BpuTk;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BpuTkController extends Controller
{
    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $query = BpuTk::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nomor_identitas', 'like', "%{$s}%")
                    ->orWhere('nama_lengkap', 'like', "%{$s}%");
            });
        }

        if ($request->filled('bpu_status')) {
            match ($request->bpu_status) {
                'lengkap' => $this->scopeComplete($query),
                'belum_lengkap' => $this->scopeIncomplete($query),
                'tgl_lahir_kosong' => $query->whereNull('tanggal_lahir'),
                'kontak_kosong' => $query->where(function ($q) {
                    $q->whereNull('handphone')->orWhere('handphone', '')
                        ->orWhereNull('email')->orWhere('email', '');
                }),
                'jenis_pekerjaan_kosong' => $query->where(function ($q) {
                    $q->whereNull('jenis_pekerjaan_1')->orWhere('jenis_pekerjaan_1', '');
                }),
                'lokasi_pekerjaan_kosong' => $query->where(function ($q) {
                    $q->whereNull('lokasi_pekerjaan')->orWhere('lokasi_pekerjaan', '');
                }),
                'upah_kosong' => $query->whereNull('upah'),
                default => null,
            };
        }

        $total = (clone $query)->count();

        // Hitung linked to master via subquery join
        $nikInMaster = Employee::select('nik_ktp')->whereNotNull('nik_ktp')
            ->union(Employee::select('nik')->whereNotNull('nik'));
        $linkedCount = (clone $query)->whereIn('nomor_identitas', Employee::selectRaw('nik_ktp')->whereNotNull('nik_ktp')
            ->union(Employee::selectRaw('nik')->whereNotNull('nik')))->count();
        $notLinkedCount = $total - $linkedCount;

        $completeCount = (clone $query)->where(function ($q) {
            $this->scopeComplete($q);
        })->count();

        $summary = [
            'total'       => $total,
            'linked'      => $linkedCount,
            'not_linked'  => $notLinkedCount,
            'lengkap'     => $completeCount,
        ];

        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000])) {
            $perPage = 15;
        }

        $records = $query->orderBy('nama_lengkap')
            ->paginate($perPage)
            ->appends($request->query());

        // Tandai mana yang ada di master (batch lookup agar tidak N+1)
        $masterNiks = Employee::whereIn('nik_ktp', $records->pluck('nomor_identitas'))
            ->orWhereIn('nik', $records->pluck('nomor_identitas'))
            ->get(['nik', 'nik_ktp'])
            ->flatMap(fn ($e) => array_filter([$e->nik, $e->nik_ktp]))
            ->unique()
            ->flip()
            ->toArray();

        return view('employee.bpu-tk', compact('records', 'summary', 'masterNiks'));
    }

    // -------------------------------------------------------------------------
    // Create / Store (manual input)
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_identitas'   => 'required|digits:16|unique:bpu_tk,nomor_identitas',
            'nama_lengkap'      => 'required|string|max:255',
            'tanggal_lahir'     => 'nullable|date',
            'handphone'         => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'jenis_pekerjaan_1' => 'nullable|string|max:255',
            'jenis_pekerjaan_2' => 'nullable|string|max:255',
            'lokasi_pekerjaan'  => 'nullable|string|max:255',
            'upah'              => 'nullable|integer|min:0',
            'kode_paket'        => 'nullable|string|max:10',
            'bulan_iuran'       => 'nullable|integer|min:1|max:12',
        ], [
            'nomor_identitas.required' => 'Nomor identitas wajib diisi.',
            'nomor_identitas.digits'   => 'Nomor identitas harus 16 angka.',
            'nomor_identitas.unique'   => 'Nomor identitas sudah terdaftar di BPU TK.',
            'nama_lengkap.required'    => 'Nama lengkap wajib diisi.',
        ]);

        $validated['kode_paket']    = $validated['kode_paket'] ?: 'T';
        $validated['bulan_iuran']   = $validated['bulan_iuran'] ?: 1;

        $record = BpuTk::create($validated);

        ActivityLog::log('create', 'BpuTk', $record->nomor_identitas, 'Tambah data BPU TK: ' . $record->nama_lengkap, [], $validated);

        return redirect()->route('employee.bpu-tk')->with('success', 'Data BPU TK berhasil ditambahkan.');
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function edit(BpuTk $bpuTk)
    {
        return view('employee.bpu-tk-edit', compact('bpuTk'));
    }

    public function update(Request $request, BpuTk $bpuTk)
    {
        $validated = $request->validate([
            'nomor_identitas'   => 'required|digits:16|unique:bpu_tk,nomor_identitas,' . $bpuTk->id,
            'nama_lengkap'      => 'required|string|max:255',
            'tanggal_lahir'     => 'nullable|date',
            'handphone'         => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'jenis_pekerjaan_1' => 'nullable|string|max:255',
            'jenis_pekerjaan_2' => 'nullable|string|max:255',
            'lokasi_pekerjaan'  => 'nullable|string|max:255',
            'upah'              => 'nullable|integer|min:0',
            'kode_paket'        => 'nullable|string|max:10',
            'bulan_iuran'       => 'nullable|integer|min:1|max:12',
        ]);

        $validated['kode_paket']  = $validated['kode_paket'] ?: 'T';
        $validated['bulan_iuran'] = $validated['bulan_iuran'] ?: 1;

        $old = $bpuTk->getAttributes();
        $bpuTk->update($validated);

        ActivityLog::log('update', 'BpuTk', $bpuTk->nomor_identitas, 'Update data BPU TK: ' . $bpuTk->nama_lengkap, $old, $validated);

        return redirect()->route('employee.bpu-tk')->with('success', 'Data BPU TK berhasil diperbarui.');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function destroy(BpuTk $bpuTk)
    {
        $old = $bpuTk->getAttributes();
        $nama = $bpuTk->nama_lengkap;
        $nik  = $bpuTk->nomor_identitas;

        $bpuTk->delete();

        ActivityLog::log('delete', 'BpuTk', $nik, 'Hapus data BPU TK: ' . $nama, $old, null);

        return redirect()->route('employee.bpu-tk')->with('success', 'Data BPU TK berhasil dihapus.');
    }

    // -------------------------------------------------------------------------
    // Import
    // -------------------------------------------------------------------------

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        $file      = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $rows      = in_array($extension, ['xlsx', 'xls'])
            ? $this->readExcel($file->getRealPath())
            : $this->readCsv($file->getRealPath());

        if (empty($rows)) {
            return redirect()->route('employee.bpu-tk')->with('error', 'File import BPU TK kosong.');
        }

        $headers   = array_map(fn ($h) => strtoupper(trim((string) $h)), $rows[0]);
        $columnMap = $this->detectColumnMap($headers);

        if (!$columnMap) {
            return redirect()->route('employee.bpu-tk')
                ->with('error', 'Format file tidak valid. Pastikan header sesuai template BPU TK (NOMOR_IDENTITAS, NAMA_LENGKAP, dll).');
        }

        $inserted  = 0;
        $updated   = 0;
        $warnings  = [];
        $errors    = [];
        $rowNumber = 1;

        foreach (array_slice($rows, 1) as $row) {
            $rowNumber++;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $nik = preg_replace('/\D+/', '', $this->cell($row, $columnMap['nomor_identitas']));

            if (!preg_match('/^[0-9]{16}$/', $nik)) {
                $errors[] = "Baris {$rowNumber}: NOMOR_IDENTITAS harus 16 angka (nilai: {$nik}).";
                continue;
            }

            // Cek apakah ada di master — hanya WARNING, bukan error
            $inMaster = Employee::where('nik', $nik)->orWhere('nik_ktp', $nik)->exists();
            if (!$inMaster) {
                $warnings[] = "Baris {$rowNumber}: NIK {$nik} belum ada di master data karyawan — data tetap disimpan.";
            }

            $data = [
                'nomor_identitas'   => $nik,
                'nama_lengkap'      => $this->cell($row, $columnMap['nama_lengkap']) ?: null,
                'tanggal_lahir'     => $this->parseDate($this->cell($row, $columnMap['tgl_lahir'])),
                'handphone'         => $this->cell($row, $columnMap['handphone']) ?: null,
                'email'             => $this->cell($row, $columnMap['email']) ?: null,
                'jenis_pekerjaan_1' => $this->cell($row, $columnMap['jenis_pekerjaan_1']) ?: null,
                'jenis_pekerjaan_2' => $this->cell($row, $columnMap['jenis_pekerjaan_2']) ?: null,
                'lokasi_pekerjaan'  => $this->cell($row, $columnMap['lokasi_pekerjaan']) ?: null,
                'upah'              => $this->parseInteger($this->cell($row, $columnMap['upah'])),
                'kode_paket'        => $this->cell($row, $columnMap['kode_paket']) ?: 'T',
                'bulan_iuran'       => $this->parseInteger($this->cell($row, $columnMap['bulan_iuran'])) ?: 1,
            ];

            $existing = BpuTk::where('nomor_identitas', $nik)->first();

            if ($existing) {
                // Hanya update field yang tidak kosong di file
                $toUpdate = array_filter($data, fn ($v) => $v !== null && $v !== '');
                unset($toUpdate['nomor_identitas']);
                if (!empty($toUpdate)) {
                    $existing->update($toUpdate);
                }
                $updated++;
            } else {
                BpuTk::create($data);
                $inserted++;
            }
        }

        $total = $inserted + $updated;

        if ($total > 0) {
            ActivityLog::log(
                'import', 'BpuTk', 'bpu-tk',
                "Import BPU TK: {$inserted} baru, {$updated} update",
                [], ['inserted' => $inserted, 'updated' => $updated, 'warnings' => $warnings, 'errors' => $errors]
            );
        }

        $msg = "Import selesai. Baru: {$inserted}, Update: {$updated}, Error: " . count($errors);
        if (count($warnings) > 0) {
            $msg .= ', Peringatan: ' . count($warnings) . ' data belum ada di master';
        }

        return redirect()->route('employee.bpu-tk')
            ->with('success', $msg)
            ->with('bpu_tk_warnings', $warnings)
            ->with('bpu_tk_errors', $errors);
    }

    // -------------------------------------------------------------------------
    // Export
    // -------------------------------------------------------------------------

    public function export(Request $request)
    {
        $query = BpuTk::query();
        $this->applyFilters($query, $request);

        $records  = $query->orderBy('nomor_identitas')->get();
        $headers  = ['NOMOR_IDENTITAS', 'NAMA_LENGKAP', 'TGL_LAHIR', 'HANDPHONE', 'EMAIL',
                     'JENIS_PEKERJAAN_1', 'JENIS_PEKERJAAN_2', 'LOKASI_PEKERJAAN', 'UPAH', 'KODE_PAKET', 'BULAN_IURAN'];
        $rows     = $records->map(fn ($r) => $this->toExportRow($r))->toArray();
        $filename = 'BPU_TK_' . date('Y-m-d_His') . '.xlsx';
        $widths   = [22, 28, 14, 16, 28, 22, 22, 22, 14, 12, 12];

        return response()->streamDownload(function () use ($headers, $rows, $widths) {
            $this->renderXlsx($headers, $rows, $widths, 'Data TK Baru');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // -------------------------------------------------------------------------
    // Download Template
    // -------------------------------------------------------------------------

    public function downloadTemplate()
    {
        $headers  = ['NOMOR_IDENTITAS', 'NAMA_LENGKAP', 'TGL_LAHIR', 'HANDPHONE', 'EMAIL',
                     'JENIS_PEKERJAAN_1', 'JENIS_PEKERJAAN_2', 'LOKASI_PEKERJAAN', 'UPAH', 'KODE_PAKET', 'BULAN_IURAN'];
        $sample   = ['1234567890123456', 'JOHN DOE', '20-01-1990', '081234567890', 'john@email.com',
                     'Kurir', '', 'Jakarta Selatan', '2000000', 'T', '1'];
        $widths   = [22, 28, 14, 16, 28, 22, 22, 22, 14, 12, 12];
        $filename = 'Template_BPU_TK.xlsx';

        return response()->streamDownload(function () use ($headers, $sample, $widths) {
            $this->renderXlsx($headers, [$sample], $widths, 'Data TK Baru');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function applyFilters(\Illuminate\Database\Eloquent\Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nomor_identitas', 'like', "%{$s}%")
                    ->orWhere('nama_lengkap', 'like', "%{$s}%");
            });
        }

        if ($request->filled('bpu_status')) {
            match ($request->bpu_status) {
                'lengkap'               => $this->scopeComplete($query),
                'belum_lengkap'         => $this->scopeIncomplete($query),
                'tgl_lahir_kosong'      => $query->whereNull('tanggal_lahir'),
                'kontak_kosong'         => $query->where(fn ($q) => $q->whereNull('handphone')->orWhere('handphone', '')->orWhereNull('email')->orWhere('email', '')),
                'jenis_pekerjaan_kosong' => $query->where(fn ($q) => $q->whereNull('jenis_pekerjaan_1')->orWhere('jenis_pekerjaan_1', '')),
                'lokasi_pekerjaan_kosong' => $query->where(fn ($q) => $q->whereNull('lokasi_pekerjaan')->orWhere('lokasi_pekerjaan', '')),
                'upah_kosong'           => $query->whereNull('upah'),
                default                 => null,
            };
        }
    }

    private function scopeComplete(\Illuminate\Database\Eloquent\Builder $q): void
    {
        $q->whereNotNull('nomor_identitas')->where('nomor_identitas', '<>', '')
            ->whereNotNull('nama_lengkap')->where('nama_lengkap', '<>', '')
            ->whereNotNull('tanggal_lahir')
            ->whereNotNull('handphone')->where('handphone', '<>', '')
            ->whereNotNull('email')->where('email', '<>', '')
            ->whereNotNull('jenis_pekerjaan_1')->where('jenis_pekerjaan_1', '<>', '')
            ->whereNotNull('lokasi_pekerjaan')->where('lokasi_pekerjaan', '<>', '')
            ->whereNotNull('upah')
            ->whereNotNull('kode_paket')->where('kode_paket', '<>', '')
            ->whereNotNull('bulan_iuran');
    }

    private function scopeIncomplete(\Illuminate\Database\Eloquent\Builder $q): void
    {
        $q->where(function ($q) {
            $q->whereNull('nama_lengkap')->orWhere('nama_lengkap', '')
                ->orWhereNull('tanggal_lahir')
                ->orWhereNull('handphone')->orWhere('handphone', '')
                ->orWhereNull('email')->orWhere('email', '')
                ->orWhereNull('jenis_pekerjaan_1')->orWhere('jenis_pekerjaan_1', '')
                ->orWhereNull('lokasi_pekerjaan')->orWhere('lokasi_pekerjaan', '')
                ->orWhereNull('upah')
                ->orWhereNull('kode_paket')->orWhere('kode_paket', '')
                ->orWhereNull('bulan_iuran');
        });
    }

    private function toExportRow(BpuTk $r): array
    {
        return [
            $r->nomor_identitas,
            $r->nama_lengkap ?? '',
            $r->tanggal_lahir ? $r->tanggal_lahir->format('d-m-Y') : '',
            $r->handphone ?? '',
            $r->email ?? '',
            $r->jenis_pekerjaan_1 ?? '',
            $r->jenis_pekerjaan_2 ?? '',
            $r->lokasi_pekerjaan ?? '',
            $r->upah ?? '',
            $r->kode_paket ?? 'T',
            $r->bulan_iuran ?? 1,
        ];
    }

    private function detectColumnMap(array $headers): ?array
    {
        $aliases = [
            'nomor_identitas'    => ['NOMOR_IDENTITAS', 'NO E-KTP', 'NO EKTP', 'NIK KTP', 'NIK'],
            'nama_lengkap'       => ['NAMA_LENGKAP', 'NAMA LENGKAP', 'NAMA'],
            'tgl_lahir'          => ['TGL_LAHIR', 'TGL LAHIR', 'TANGGAL LAHIR', 'TANGGAL_LAHIR'],
            'handphone'          => ['HANDPHONE', 'NO HP', 'HP', 'NO_HP'],
            'email'              => ['EMAIL', 'E-MAIL'],
            'jenis_pekerjaan_1'  => ['JENIS_PEKERJAAN_1', 'JENIS PEKERJAAN 1'],
            'jenis_pekerjaan_2'  => ['JENIS_PEKERJAAN_2', 'JENIS PEKERJAAN 2'],
            'lokasi_pekerjaan'   => ['LOKASI_PEKERJAAN', 'LOKASI PEKERJAAN'],
            'upah'               => ['UPAH'],
            'kode_paket'         => ['KODE_PAKET', 'KODE PAKET'],
            'bulan_iuran'        => ['BULAN_IURAN', 'BULAN IURAN'],
        ];

        $map = [];
        foreach ($aliases as $field => $candidates) {
            $map[$field] = null;
            foreach ($candidates as $candidate) {
                $idx = array_search($candidate, $headers, true);
                if ($idx !== false) {
                    $map[$field] = $idx;
                    break;
                }
            }
        }

        $required = ['nomor_identitas', 'nama_lengkap', 'tgl_lahir', 'handphone', 'email',
                     'jenis_pekerjaan_1', 'lokasi_pekerjaan', 'upah', 'kode_paket', 'bulan_iuran'];
        foreach ($required as $f) {
            if ($map[$f] === null) {
                return null;
            }
        }

        return $map;
    }

    private function cell(array $row, ?int $index): string
    {
        if ($index === null) {
            return '';
        }
        return trim((string) ($row[$index] ?? ''));
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $v) {
            if (trim((string) $v) !== '') {
                return false;
            }
        }
        return true;
    }

    private function parseInteger(string $value): ?int
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        return $digits === '' ? null : (int) $digits;
    }

    private function parseDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'Y/m/d', 'd-m-y', 'd/m/y'] as $fmt) {
            $date = \DateTime::createFromFormat($fmt, $value);
            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d');
            }
        }
        return null;
    }

    private function readExcel(string $path): array
    {
        // Baca langsung dari XML di dalam XLSX (ZIP) agar angka besar seperti NIK 16 digit
        // tidak dipotong oleh PHP float precision (Excel 15-digit limit).
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        // Shared strings (sel teks)
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $ssDom = new \DOMDocument();
            @$ssDom->loadXML($ssXml);
            $ssDom->preserveWhiteSpace = false;
            foreach ($ssDom->getElementsByTagName('si') as $si) {
                $tNodes = $si->getElementsByTagName('t');
                $text = '';
                foreach ($tNodes as $t) {
                    $text .= $t->nodeValue;
                }
                $sharedStrings[] = $text;
            }
        }

        // Sheet data
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $dom = new \DOMDocument();
        @$dom->loadXML($sheetXml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('ss', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = [];
        foreach ($xpath->query('//ss:row') as $sheetRow) {
            $row = [];
            $cells = $sheetRow->getElementsByTagName('c');

            for ($i = 0; $i < $cells->length; $i++) {
                $cell = $cells->item($i);
                if (!($cell instanceof \DOMElement)) {
                    continue;
                }

                $ref   = $cell->getAttribute('r');
                $col   = preg_replace('/\d+/', '', $ref);
                $colIdx = $col
                    ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($col) - 1
                    : $i;

                $type  = $cell->getAttribute('t');
                $vNode = $cell->getElementsByTagName('v');
                $value = '';

                if ($vNode->length > 0) {
                    $raw = $vNode->item(0)->nodeValue;
                    if ($type === 's') {
                        // Shared string — teks asli, presisi penuh
                        $value = $sharedStrings[(int) $raw] ?? '';
                    } elseif ($type === 'str' || $type === 'inlineStr') {
                        $value = $raw;
                    } else {
                        // Numerik — simpan sebagai string mentah dari XML
                        // (sudah dalam format integer atau float decimal, bukan scientific notation)
                        $value = $raw;
                        // Normalkan jika Excel menyimpan dalam scientific notation (e.g. 1.20602E+15)
                        if (stripos($value, 'E') !== false) {
                            $value = number_format((float) $value, 0, '.', '');
                        }
                        // Buang desimal jika bilangan bulat (e.g. "1206020303950004.0" → "1206020303950004")
                        if (str_contains($value, '.')) {
                            $value = rtrim(rtrim($value, '0'), '.');
                        }
                    }
                } elseif ($type === 'inlineStr') {
                    $tNode = $cell->getElementsByTagName('t');
                    $value = $tNode->length > 0 ? $tNode->item(0)->nodeValue : '';
                }

                $row[$colIdx] = $value;
            }

            if (!empty($row)) {
                ksort($row);
                $max = max(array_keys($row));
                $row = array_replace(array_fill(0, $max + 1, ''), $row);
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function readCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');
        while ($row = fgetcsv($handle)) {
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    private function renderXlsx(array $headers, array $rows, array $columnWidths = [], string $sheetName = 'Sheet1'): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);

        // Header row
        $col = 1;
        foreach ($headers as $header) {
            $cell = $sheet->getCellByColumnAndRow($col, 1);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true);
            $cell->getStyle()->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFA500');
            $col++;
        }

        // Data rows — kolom 1 (NOMOR_IDENTITAS) wajib tipe STRING agar NIK 16 digit tidak dipotong Excel
        $rowIndex = 2;
        foreach ($rows as $row) {
            $col = 1;
            foreach ($row as $value) {
                $c = $sheet->getCellByColumnAndRow($col, $rowIndex);
                if ($col === 1) {
                    $c->setValueExplicit((string) $value, DataType::TYPE_STRING);
                } else {
                    $c->setValue($value);
                }
                $col++;
            }
            $rowIndex++;
        }

        // Format seluruh kolom A (NOMOR_IDENTITAS) sebagai teks (@) — termasuk sel kosong yang diisi user
        $lastRow = max($rowIndex - 1, 1000);
        $sheet->getStyle('A2:A' . $lastRow)
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Column widths
        foreach ($columnWidths as $i => $width) {
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth($width);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}
