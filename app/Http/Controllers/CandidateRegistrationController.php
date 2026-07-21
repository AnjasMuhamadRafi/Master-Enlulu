<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CandidateRegistration;
use App\Models\Employee;
use App\Services\CandidateDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CandidateRegistrationController extends Controller
{
    private const DOCUMENTS = [
        'ktp' => 'ktp_path',
        'kk' => 'kk_path',
        'ijazah' => 'diploma_path',
        'cv-lamaran' => 'cv_path',
        'legacy-surat-lamaran' => 'application_letter_path',
    ];

    public function __construct(private readonly CandidateDocumentService $documents)
    {
    }

    public function index(Request $request)
    {
        $query = $this->filteredQuery($request)->with(['creator', 'employee']);

        $registrations = $query->latest()->paginate(20)->withQueryString();

        return view('candidate-registration.index', compact('registrations'));
    }

    public function export(Request $request)
    {
        $registrations = $this->filteredQuery($request)
            ->orderByDesc('submitted_at')
            ->get();

        $headers = [
            'NO',
            'NAMA LENGKAP',
            'NIK KTP',
            'NO. HP',
            'EMAIL',
            'TEMPAT LAHIR',
            'TANGGAL LAHIR',
            'NAMA IBU KANDUNG',
            'LOKASI KERJA',
            'BANK',
            'NO. REKENING',
            'A.N. PEMILIK REKENING',
            'KTP',
            'KK',
            'IJAZAH',
            'CV / SURAT LAMARAN',
            'STATUS',
            'TANGGAL PENGISIAN',
            'DITERAPKAN KE MASTER',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kelengkapan Data');

        foreach ($headers as $index => $header) {
            $column = $index + 1;
            $cell = Coordinate::stringFromColumnIndex($column) . '1';
            $sheet->setCellValueExplicit($cell, $header, DataType::TYPE_STRING);
            $sheet->getStyle($cell)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('F46B35');
            $sheet->getStyle([$column, 1])->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        }

        foreach ($registrations as $rowIndex => $registration) {
            $values = [
                $rowIndex + 1,
                $registration->full_name,
                $registration->nik_ktp,
                $registration->phone,
                $registration->email,
                $registration->birth_place,
                $registration->birth_date?->format('d/m/Y'),
                $registration->mother_name,
                $registration->work_location,
                $registration->bank_name,
                $registration->bank_account_number,
                $registration->bank_account_holder,
                $registration->ktp_path ? 'ADA' : 'TIDAK ADA',
                $registration->kk_path ? 'ADA' : 'TIDAK ADA',
                $registration->diploma_path ? 'ADA' : 'TIDAK ADA',
                $registration->cv_path ? 'ADA' : 'TIDAK ADA',
                strtoupper($registration->status),
                $registration->submitted_at?->format('d/m/Y H:i'),
                $registration->applied_at?->format('d/m/Y H:i') ?: 'BELUM',
            ];

            foreach ($values as $columnIndex => $value) {
                $cell = Coordinate::stringFromColumnIndex($columnIndex + 1) . ($rowIndex + 2);
                $sheet->setCellValueExplicit(
                    $cell,
                    (string) ($value ?? ''),
                    DataType::TYPE_STRING
                );
            }
        }

        foreach (range('A', 'S') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->freezePane('A2');
        $filename = 'Kelengkapan-Data-Kandidat-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function show(CandidateRegistration $candidateRegistration)
    {
        $candidateRegistration->load(['creator', 'employee']);

        return view('candidate-registration.show', compact('candidateRegistration'));
    }

    public function apply(CandidateRegistration $candidateRegistration)
    {
        abort_unless($candidateRegistration->status === 'submitted', 422, 'Data kandidat belum lengkap.');

        $employee = $candidateRegistration->employee;

        if (!$employee) {
            $employee = Employee::query()
                ->where('nik', $candidateRegistration->nik_ktp)
                ->orWhere('nik_ktp', $candidateRegistration->nik_ktp)
                ->first();
        }

        $data = [
            'nik_ktp' => $candidateRegistration->nik_ktp,
            'nama_ktp' => $candidateRegistration->full_name,
            'nama_lengkap' => $candidateRegistration->full_name,
            'nama_ibu_kandung' => $candidateRegistration->mother_name,
            'no_hp' => $candidateRegistration->phone,
            'email' => $candidateRegistration->email,
            'tempat_lahir' => $candidateRegistration->birth_place,
            'tanggal_lahir' => $candidateRegistration->birth_date,
            'penempatan' => $candidateRegistration->work_location,
            'nama_bank' => $candidateRegistration->bank_name,
            'no_rekening' => $candidateRegistration->bank_account_number,
            'nama_di_rekening' => $candidateRegistration->bank_account_holder,
        ];

        $documentColumns = [
            'ktp_path' => 'dokumen_ktp',
            'kk_path' => 'dokumen_kk',
            'diploma_path' => 'dokumen_ijazah',
            'cv_path' => 'dokumen_cv',
        ];

        foreach ($documentColumns as $sourceColumn => $targetColumn) {
            $sourcePath = $candidateRegistration->{$sourceColumn};
            if (!$sourcePath || !Storage::disk('local')->exists($sourcePath)) {
                continue;
            }

            $targetPath = 'employee_documents/' . $candidateRegistration->nik_ktp . '/' . $targetColumn . '-' . basename($sourcePath);
            Storage::disk('local')->copy($sourcePath, $targetPath);
            $data[$targetColumn] = $targetPath;
        }

        if ($employee) {
            $oldValues = $employee->only(array_keys($data));
            $employee->update($data);
        } else {
            $oldValues = [];
            $employee = Employee::create($data + [
                'nik' => $candidateRegistration->nik_ktp,
                'status' => 'Training',
            ]);
        }

        $candidateRegistration->update([
            'employee_nik' => $employee->nik,
            'applied_at' => now(),
        ]);

        ActivityLog::log(
            'update',
            'Employee',
            $employee->nik,
            'Menerapkan data kandidat ke master karyawan: ' . $employee->nama_lengkap,
            $oldValues,
            $data
        );

        return back()->with('success', 'Data berhasil diterapkan ke master karyawan.');
    }

    public function download(CandidateRegistration $candidateRegistration, string $document)
    {
        abort_unless(array_key_exists($document, self::DOCUMENTS), 404);

        $column = self::DOCUMENTS[$document];
        $path = $candidateRegistration->{$column};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return $this->documents->download($path);
    }

    public function destroy(CandidateRegistration $candidateRegistration)
    {
        foreach (self::DOCUMENTS as $column) {
            if ($candidateRegistration->{$column}) {
                Storage::disk('local')->delete($candidateRegistration->{$column});
            }
        }

        $name = $candidateRegistration->candidate_name;
        $id = (string) $candidateRegistration->id;
        $candidateRegistration->delete();

        ActivityLog::log(
            'delete',
            'CandidateRegistration',
            $id,
            'Menghapus link dan data kelengkapan kandidat: ' . $name
        );

        return redirect()->route('candidate-registration.index')
            ->with('success', 'Data kandidat berhasil dihapus.');
    }

    private function filteredQuery(Request $request)
    {
        $query = CandidateRegistration::query();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($candidateQuery) use ($search) {
                $candidateQuery
                    ->where('candidate_name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('nik_ktp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query;
    }
}
