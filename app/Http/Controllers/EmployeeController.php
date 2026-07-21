<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\User;
use App\Services\ContractDocumentService;
use App\Services\CandidateDocumentService;
use App\Services\PksNumberService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeController extends Controller
{
    private const IMPORT_PREVIEW_LIMIT = 100;
    private const DOCUMENT_UPLOADS = [
        'file_ktp' => ['column' => 'dokumen_ktp', 'label' => 'ktp'],
        'file_kk' => ['column' => 'dokumen_kk', 'label' => 'kk'],
        'file_ijazah' => ['column' => 'dokumen_ijazah', 'label' => 'ijazah'],
        'file_cv_lamaran' => ['column' => 'dokumen_cv', 'label' => 'cv-lamaran'],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::query();
        
        // Access control: Filter by user role
        /** @var User|null $user */
        $user = $this->currentUser();
        if ($user && $user->isAdminPic()) {
            $managedPositions = $user->getManagedPositions();
            $query->whereIn('posisi', $managedPositions);
        }
        // Super Admin dan role lain bisa lihat semua
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%$search%")
                  ->orWhere('nama_lengkap', 'like', "%$search%")
                  ->orWhere('posisi', 'like', "%$search%");
            });
        }
        
        // Filter by penempatan (using like for partial match)
        if ($request->filled('penempatan')) {
            $query->where('penempatan', 'like', "%{$request->penempatan}%");
        }
        
        // Filter by posisi (using like for partial match)
        if ($request->filled('posisi')) {
            $query->where('posisi', 'like', "%{$request->posisi}%");
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Get per_page parameter with allowed values (cast to int for comparison)
        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000])) {
            $perPage = 15;
        }
        $employees = $query->paginate($perPage)->appends($request->query());
        $penempatan = Employee::distinct()->pluck('penempatan')->filter();
        $posisi = Employee::distinct()->pluck('posisi')->filter();
        
        return view('employee.index', compact('employees', 'penempatan', 'posisi'));
    }

    private function currentUser(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $adminPicRoles = $this->adminPicRoleOptions();
        $operationalPositions = $this->operationalPositionOptions();

        return view('employee.create', compact('adminPicRoles', 'operationalPositions'));
    }

    private function adminPicRoleOptions(): array
    {
        return collect(config('positions.admin_pic_roles', []))
            ->merge(
                Employee::query()
                    ->whereNotNull('posisi')
                    ->where('posisi', 'like', 'ADMIN/PIC-%')
                    ->distinct()
                    ->pluck('posisi')
            )
            ->map(fn ($position) => $this->normalizePosition($position))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function operationalPositionOptions(): array
    {
        return collect(config('positions.operational_positions', []))
            ->merge(
                collect(config('positions.admin_pic_departments', []))
                    ->flatten()
            )
            ->merge(
                Employee::query()
                    ->whereNotNull('posisi')
                    ->distinct()
                    ->pluck('posisi')
            )
            ->map(fn ($position) => $this->normalizePosition($position))
            ->filter(fn ($position) => $position !== '' && !str_starts_with($position, 'ADMIN/PIC-'))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function normalizePosition(?string $position): string
    {
        return trim(strtoupper((string) $position));
    }

    /**
     * Aturan validasi field master data karyawan (dipakai store & update).
     * Hanya nama sesuai KTP, tempat lahir, dan tanggal lahir yang wajib.
     * NIK KTP divalidasi terpisah pada store/update.
     */
    private function masterDataRules(): array
    {
        return [
            // Identitas & pekerjaan
            'nik_enlulu' => 'nullable|string|max:50',
            'nik_os' => 'nullable|string|max:50',
            'nama_ktp' => 'required|string|max:100',
            'nama_ibu_kandung' => 'nullable|string|max:150',
            'posisi' => 'nullable|string|max:100',
            'penempatan' => 'nullable|string|max:100',
            'type_lokasi' => 'nullable|string|max:50',
            'area_kerja' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:30',
            'nama_bank' => 'nullable|string|max:50',
            'nama_di_rekening' => 'nullable|string|max:100',
            'status' => 'nullable|in:' . implode(',', Employee::EMPLOYMENT_STATUSES),
            'note1' => 'nullable|string|max:500',

            // A. Biodata
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'nullable|in:Pria,Wanita',
            'agama' => 'nullable|string|max:30',
            'pendidikan' => 'nullable|string|max:30',
            'status_pernikahan' => 'nullable|string|max:30',
            'jumlah_anak' => 'nullable|integer|min:0|max:50',
            'alamat' => 'nullable|string|max:255',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kota' => 'nullable|string|max:100',
            'propinsi' => 'nullable|string|max:100',
            'status_tempat_tinggal' => 'nullable|string|max:30',
            'no_hp' => 'nullable|string|max:20',
            'no_kk' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'no_bpjs_tk' => 'nullable|string|max:30',
            'no_bpjs_kesehatan' => 'nullable|string|max:30',
            'keterangan_biodata' => 'nullable|string|max:500',

            // B. Emergency Contact
            'ec_nama' => 'nullable|string|max:100',
            'ec_alamat' => 'nullable|string|max:255',
            'ec_no_hp' => 'nullable|string|max:20',
            'ec_hubungan' => 'nullable|string|max:50',

            // E. Penempatan Kerja (opsional)
            'nama_customer' => 'nullable|string|max:150',
            'tanggal_masuk' => 'nullable|date',
            'tanggal_keluar' => 'nullable|date',
            'tanggal_perpanjangan_terakhir' => 'nullable|date',
            'keterangan_perpanjangan' => 'nullable|string|max:500',
            'no_pks_masuk' => 'nullable|string|max:100',
            'no_pks_perpanjangan' => 'nullable|string|max:100',
            'nama_perekrut' => 'nullable|string|max:100',
        ];
    }

    /**
     * Pesan validasi berbahasa Indonesia.
     */
    private function validationMessages(): array
    {
        return [
            'nik.required' => 'NIK harus diisi',
            'nik.unique' => 'NIK sudah terdaftar dalam sistem',
            'nik.regex' => 'NIK harus terdiri dari 16 angka',
            'nik.digits' => 'NIK harus terdiri dari 16 angka',
            'nama_ktp.required' => 'Nama harus diisi',
            'status.in' => 'Status tidak valid',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid',
            'email.email' => 'Format e-mail tidak valid',
            'foto.required' => 'Foto wajah harus diunggah',
            'foto.image' => 'File foto harus berupa gambar',
            'foto.max' => 'Ukuran foto maksimal 2 MB',
            'required' => 'Field ini harus diisi',
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->normalizeCustomerField($request);
        $this->normalizeRequestStatus($request);

        $rules = array_merge([
            'nik' => 'required|unique:employees,nik|regex:/^[0-9]{16}$/|digits:16',
            'foto' => 'nullable|image|max:10240',
        ], $this->masterDataRules());

        $validated = $request->validate($rules, $this->validationMessages());
        $validated['posisi'] = $this->normalizePosition($validated['posisi'] ?? '') ?: null;
        if (($validated['status'] ?? null) === null) {
            unset($validated['status']);
        }

        // Auto-fill nik_ktp dan nama_lengkap with nik and nama_ktp respectively
        $validated['nik_ktp'] = $validated['nik'];
        $validated['nama_lengkap'] = $validated['nama_ktp'];

        // Upload & konversi foto wajah ke JPG
        if ($request->hasFile('foto')) {
            $validated['foto'] = $this->storePhotoAsJpg($request->file('foto'));
        }

        $employee = Employee::create($validated);
        
        // Log aktivitas
        ActivityLog::log(
            'create',
            'Employee',
            $employee->nik,
            'Karyawan baru ditambahkan: ' . $employee->nama_lengkap,
            null,
            $validated
        );

        return redirect()->route('employee.index')
                        ->with('success', 'Data karyawan berhasil ditambahkan')
                        ->with('manual_created', [
                            'nik' => $employee->nik,
                            'nama' => $employee->nama_lengkap
                        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        // Access control: Check if user can view this employee
        /** @var User|null $user */
        $user = $this->currentUser();
        if ($user && $user->isAdminPic()) {
            if (!$user->canAccessPosition($employee->posisi)) {
                abort(403, 'Anda tidak memiliki akses ke data karyawan ini.');
            }
        }
        
        return view('employee.show', compact('employee'));
    }

    public function downloadDocument(Employee $employee, string $document)
    {
        /** @var User|null $user */
        $user = $this->currentUser();
        if ($user && $user->isAdminPic() && !$user->canAccessPosition($employee->posisi)) {
            abort(403, 'Anda tidak memiliki akses ke dokumen karyawan ini.');
        }

        $documents = [
            'ktp' => 'dokumen_ktp',
            'kk' => 'dokumen_kk',
            'ijazah' => 'dokumen_ijazah',
            'cv' => 'dokumen_cv',
            'surat-lamaran' => 'dokumen_surat_lamaran',
        ];

        abort_unless(array_key_exists($document, $documents), 404);

        $path = $employee->{$documents[$document]};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return app(CandidateDocumentService::class)->download($path);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        // Access control: Check if user can edit this employee
        /** @var User|null $user */
        $user = $this->currentUser();
        if ($user && $user->isAdminPic()) {
            if (!$user->canAccessPosition($employee->posisi)) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit data karyawan ini.');
            }
        }
        
        $adminPicRoles = $this->adminPicRoleOptions();
        $operationalPositions = $this->operationalPositionOptions();

        return view('employee.edit', compact('employee', 'adminPicRoles', 'operationalPositions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        // Access control: Check if user can update this employee
        /** @var User|null $user */
        $user = $this->currentUser();
        if ($user && $user->isAdminPic()) {
            if (!$user->canAccessPosition($employee->posisi)) {
                abort(403, 'Anda tidak memiliki akses untuk mengubah data karyawan ini.');
            }
        }
        
        $this->normalizeCustomerField($request);
        $this->normalizeRequestStatus($request);

        $rules = array_merge([
            'nik' => 'required|unique:employees,nik,' . $employee->nik . ',nik|regex:/^[0-9]{16}$/|digits:16',
            'foto' => 'nullable|image|max:10240',
            'file_ktp' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:15360',
            'file_kk' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:15360',
            'file_ijazah' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:15360',
            'file_cv_lamaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:15360',
        ], $this->masterDataRules());

        $validated = $request->validate($rules, $this->validationMessages());
        $validated['posisi'] = $this->normalizePosition($validated['posisi'] ?? '') ?: null;
        if (($validated['status'] ?? null) === null) {
            unset($validated['status']);
        }

        // Auto-fill nik_ktp dan nama_lengkap with nik and nama_ktp respectively
        $validated['nik_ktp'] = $validated['nik'];
        $validated['nama_lengkap'] = $validated['nama_ktp'];

        // Upload & konversi foto wajah baru ke JPG (hapus yang lama jika ada)
        if ($request->hasFile('foto')) {
            if ($employee->foto && Storage::disk('public')->exists($employee->foto)) {
                Storage::disk('public')->delete($employee->foto);
            }
            $validated['foto'] = $this->storePhotoAsJpg($request->file('foto'));
        } else {
            // Jangan timpa foto lama dengan null bila tidak ada upload baru
            unset($validated['foto']);
        }

        $newDocumentPaths = [];
        $oldDocumentPaths = [];
        foreach (self::DOCUMENT_UPLOADS as $input => $document) {
            unset($validated[$input]);

            if (!$request->hasFile($input)) {
                continue;
            }

            $column = $document['column'];
            $path = app(CandidateDocumentService::class)->store(
                $request->file($input),
                'employee_documents/' . $validated['nik'],
                $document['label']
            );

            $validated[$column] = $path;
            $newDocumentPaths[] = $path;

            if ($employee->{$column}) {
                $oldDocumentPaths[] = $employee->{$column};
            }
        }

        $oldValues = $employee->getAttributes();
        $becomingActive = ($oldValues['status'] ?? null) !== 'Aktif'
            && ($validated['status'] ?? null) === 'Aktif';

        try {
            DB::transaction(function () use ($employee, &$validated, $becomingActive): void {
                if ($becomingActive && empty($employee->no_pks_masuk)) {
                    $activationDate = !empty($validated['tanggal_masuk'])
                        ? Carbon::parse($validated['tanggal_masuk'])
                        : ($employee->tanggal_masuk ?: now());

                    if (empty($validated['tanggal_masuk']) && !$employee->tanggal_masuk) {
                        $validated['tanggal_masuk'] = $activationDate->format('Y-m-d');
                    }

                    $validated['no_pks_masuk'] = app(PksNumberService::class)->next($activationDate);
                }

                $employee->update($validated);
                $employee->refresh();

                if ($becomingActive && $employee->no_pks_masuk) {
                    Contract::firstOrCreate(
                        [
                            'employee_nik' => $employee->nik,
                            'contract_number' => $employee->no_pks_masuk,
                        ],
                        [
                            'contract_date' => $employee->tanggal_masuk ?: now(),
                            'start_date' => $employee->tanggal_masuk ?: now(),
                            'end_date' => $employee->tanggal_keluar,
                            'template_name' => ContractDocumentService::TEMPLATE_NAME,
                            'created_by' => auth()->id(),
                        ]
                    );
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($newDocumentPaths);

            throw $exception;
        }

        Storage::disk('local')->delete($oldDocumentPaths);
        
        // Log aktivitas
        ActivityLog::log(
            'update',
            'Employee',
            $employee->nik,
            'Data karyawan diperbarui: ' . $employee->nama_lengkap,
            $oldValues,
            $validated
        );

        // Build detailed changes with old and new values
        $changes = array_diff_assoc($validated, $oldValues);
        $changeDetails = [];
        foreach ($changes as $field => $newValue) {
            $changeDetails[$field] = [
                'old' => $oldValues[$field] ?? '-',
                'new' => $newValue
            ];
        }

        return redirect()->route('employee.index')
                        ->with('success', 'Data karyawan berhasil diperbarui')
                        ->with('manual_updated', [
                            'nik' => $employee->nik,
                            'nama' => $employee->nama_lengkap,
                            'changes' => $changeDetails
                        ]);
    }

    /**
     * Generate form dokumen dari data form (untuk preview/print)
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        // Access control: Check if user can delete this employee
        /** @var User|null $user */
        $user = $this->currentUser();
        if ($user && $user->isAdminPic()) {
            if (!$user->canAccessPosition($employee->posisi)) {
                abort(403, 'Anda tidak memiliki akses untuk menghapus data karyawan ini.');
            }
        }
        
        $data = $employee->getAttributes();
        $nama = $employee->nama_lengkap;
        $nik = $employee->nik;
        
        $employee->delete();
        
        // Log aktivitas
        ActivityLog::log(
            'delete',
            'Employee',
            $nik,
            'Karyawan dihapus: ' . $nama,
            $data,
            null
        );
        
        return redirect()->route('employee.index')
                        ->with('success', 'Data karyawan berhasil dihapus');
    }

    /**
     * Konversi foto upload ke JPG terkompresi dan simpan ke storage.
     * - Max dimensi 1200px (maintain aspect ratio)
     * - Kualitas JPG 78% → ukuran file sangat kecil di server
     */
    private function storePhotoAsJpg(\Illuminate\Http\UploadedFile $file): string
    {
        $tmpPath = $file->getRealPath();
        $mime    = $file->getMimeType() ?? '';

        $source = match (true) {
            str_contains($mime, 'jpeg') => @imagecreatefromjpeg($tmpPath),
            str_contains($mime, 'png')  => @imagecreatefrompng($tmpPath),
            str_contains($mime, 'gif')  => @imagecreatefromgif($tmpPath),
            str_contains($mime, 'webp') => @imagecreatefromwebp($tmpPath),
            str_contains($mime, 'bmp')  => @imagecreatefrombmp($tmpPath),
            default                     => @imagecreatefromjpeg($tmpPath),
        };

        // Fallback: simpan asli jika GD gagal baca
        if (!$source) {
            return $file->store('employee_photos', 'public');
        }

        $origW  = imagesx($source);
        $origH  = imagesy($source);
        $maxDim = 1200;

        if ($origW > $maxDim || $origH > $maxDim) {
            $ratio  = min($maxDim / $origW, $maxDim / $origH);
            $newW   = (int) round($origW * $ratio);
            $newH   = (int) round($origH * $ratio);
            $canvas = imagecreatetruecolor($newW, $newH);
            // Isi background putih (untuk PNG transparan)
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($source);
            $source = $canvas;
        } else {
            // Tetap flatten ke canvas baru agar PNG transparan → putih
            $canvas = imagecreatetruecolor($origW, $origH);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopy($canvas, $source, 0, 0, 0, 0, $origW, $origH);
            imagedestroy($source);
            $source = $canvas;
        }

        // Simpan ke file temp lalu upload ke storage
        $tmpJpg = tempnam(sys_get_temp_dir(), 'empphoto_') . '.jpg';
        imagejpeg($source, $tmpJpg, 78);
        imagedestroy($source);

        $storagePath = 'employee_photos/' . uniqid('photo_') . '.jpg';
        Storage::disk('public')->put($storagePath, file_get_contents($tmpJpg));
        @unlink($tmpJpg);

        return $storagePath;
    }

    /**
     * Download template CSV untuk import
     */
    public function downloadTemplate()
    {
        // Generate Excel template using native XLSX format
        $filename = 'Template_Karyawan_' . date('Y-m-d_His') . '.xlsx';
        
        return response()->streamDownload(function () {
            $this->generateExcelTemplate();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    private function generateExcelTemplate()
    {
        $columns = $this->exportColumns();
        $headers = array_merge(['NO'], array_map(fn ($c) => $c[0], $columns));

        // Contoh nilai per field untuk baris sampel
        $examples = [
            'nik_ktp' => '1234567890123456', 'nama_ktp' => 'John Doe',
            'nik_enlulu' => 'ENL-0001', 'nik_os' => 'OS-12345',
            'nama_customer' => 'PT Maju Jaya',
            'posisi' => 'Sprinter', 'type_lokasi' => 'Cabang', 'penempatan' => 'Jakarta Selatan',
            'area_kerja' => 'DKI Jakarta', 'status' => 'Aktif',
            'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '1995-05-20',
            'jenis_kelamin' => 'Pria', 'agama' => 'Islam', 'pendidikan' => 'SMA',
            'status_pernikahan' => 'Single', 'jumlah_anak' => '0',
            'alamat' => 'Jl. Semangat No.18', 'kelurahan' => 'Menteng Dalam',
            'kecamatan' => 'Tebet', 'kota' => 'Jakarta Selatan', 'propinsi' => 'DKI Jakarta',
            'status_tempat_tinggal' => 'Sewa', 'no_hp' => '081234567890',
            'no_kk' => '1234567890123456', 'email' => 'john@example.com',
            'no_bpjs_tk' => '1122334455', 'no_bpjs_kesehatan' => '0001234567',
            'keterangan_biodata' => '',
            'ec_nama' => 'Budi', 'ec_alamat' => 'Bandung', 'ec_no_hp' => '081200000000', 'ec_hubungan' => 'Orang tua',
            'nama_bank' => 'BCA', 'no_rekening' => '6830295338', 'nama_di_rekening' => 'John Doe',
            'tanggal_masuk' => '2024-01-15', 'tanggal_keluar' => '', 'tanggal_perpanjangan_terakhir' => '',
            'keterangan_perpanjangan' => '', 'no_pks_masuk' => 'PKS/2024/001', 'no_pks_perpanjangan' => '',
            'note1' => 'Contoh catatan',
            'nama_perekrut' => 'Ahmad Fauzi',
        ];

        $sampleRow = ['1'];
        foreach ($columns as $c) {
            $sampleRow[] = $examples[$c[1]] ?? '';
        }

        $this->renderSpreadsheet($headers, [$sampleRow]);
    }

    private function renderSpreadsheet(array $headers, array $rows, ?array $columnWidths = null, string $sheetTitle = 'Sheet1'): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);

        // Lebar kolom sesuai urutan exportColumns() + kolom NO di depan
        $columnWidths ??= [
            6,   // NO
            18,  // NIK KTP
            22,  // NAMA KTP
            18,  // NIK ENLULU
            18,  // NIK OS
            // A. Biodata
            15,  // TEMPAT LAHIR
            14,  // TANGGAL LAHIR
            14,  // JENIS KELAMIN
            12,  // AGAMA
            13,  // PENDIDIKAN
            20,  // STATUS PERNIKAHAN
            12,  // JUMLAH ANAK
            30,  // ALAMAT TINGGAL
            15,  // KELURAHAN
            15,  // KECAMATAN
            18,  // KOTA/KABUPATEN
            15,  // PROPINSI
            22,  // STATUS TEMPAT TINGGAL
            15,  // NO HP
            18,  // NO KK
            22,  // E-MAIL
            26,  // NO BPJS KETENAGAKERJAAN
            20,  // NO BPJS KESEHATAN
            22,  // KETERANGAN LAIN BIODATA
            // B. Emergency Contact
            22,  // EMERGENCY CONTACT NAMA
            25,  // EMERGENCY CONTACT ALAMAT
            22,  // EMERGENCY CONTACT NO HP
            14,  // HUBUNGAN
            // C. Banking
            14,  // NAMA BANK
            16,  // NO REKENING
            22,  // NAMA PEMILIK REKENING
            // E. Penempatan Kerja
            20,  // KLIEN
            18,  // JABATAN
            13,  // TYPE LOKASI
            18,  // LOKASI KERJA
            14,  // AREA KERJA
            14,  // TANGGAL MASUK
            13,  // STATUS KERJA
            14,  // TANGGAL KELUAR
            28,  // TANGGAL PERPANJANGAN TERAKHIR
            25,  // KETERANGAN PERPANJANGAN
            16,  // NO PKS MASUK
            20,  // NO PKS PERPANJANGAN
            22,  // KETERANGAN LAIN-LAIN
            // Perekrut
            20,  // NAMA PEREKRUT
        ];

        foreach ($columnWidths as $index => $width) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index + 1))->setWidth($width);
        }
        // Fallback untuk kolom di luar daftar
        for ($i = count($columnWidths); $i < count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth(16);
        }

        // Tinggi header
        $sheet->getRowDimension(1)->setRowHeight(45);

        // Header row
        foreach ($headers as $index => $header) {
            $cell = Coordinate::stringFromColumnIndex($index + 1) . '1';
            $sheet->setCellValueExplicit($cell, (string) $header, DataType::TYPE_STRING);
        }

        // Data rows
        foreach ($rows as $rowIndex => $row) {
            $rowNum = $rowIndex + 2;
            $sheet->getRowDimension($rowNum)->setRowHeight(18);
            foreach ($row as $colIndex => $value) {
                $cell = Coordinate::stringFromColumnIndex($colIndex + 1) . $rowNum;
                $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
            }
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $lastRow    = count($rows) + 1;

        // Style header: orange background, teks putih tebal, wrap teks, center
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFF6B35']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        // Style data rows: rata kiri, vertikal center
        if ($lastRow > 1) {
            $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
                'font'      => ['size' => 10],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => false,
                ],
            ]);
        }

        // Border tipis seluruh tabel
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD3D3D3']],
            ],
        ]);

        // Freeze baris header agar tetap terlihat saat scroll
        $sheet->freezePane('A2');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }
    
    private function getSheetXml(array $headers, array $data): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        
        // Column widths
        $xml .= '<cols>' . "\n";
        $widths = [18, 25, 18, 18, 16, 18, 20, 14]; // Updated untuk 8 columns (+ Status)
        foreach ($widths as $index => $width) {
            $col = chr(65 + $index);
            $xml .= '<col min="' . ($index + 1) . '" max="' . ($index + 1) . '" width="' . $width . '" customWidth="1"/>' . "\n";
        }
        $xml .= '</cols>' . "\n";
        
        $xml .= '<sheetData>' . "\n";
        
        // Header row
        $xml .= '<row r="1" ht="30" customHeight="1">' . "\n";
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index); // A, B, C, etc
            $xml .= '<c r="' . $col . '1" s="3" t="inlineStr"><is><t>' . htmlspecialchars($header) . '</t></is></c>' . "\n";
        }
        $xml .= '</row>' . "\n";
        
        // Data rows
        foreach ($data as $rowIndex => $row) {
            $xml .= '<row r="' . ($rowIndex + 2) . '" ht="20" customHeight="1">' . "\n";
            foreach ($row as $colIndex => $value) {
                $col = chr(65 + $colIndex);
                // Keep values as text to preserve leading zeros and long NIK format.
                $xml .= '<c r="' . $col . ($rowIndex + 2) . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($value) . '</t></is></c>' . "\n";
            }
            $xml .= '</row>' . "\n";
        }
        
        $xml .= '</sheetData>' . "\n";
        $xml .= '<pageMargins left="0.75" top="1" right="0.75" bottom="1" header="0.5" footer="0.5"/>' . "\n";
        $xml .= '</worksheet>' . "\n";
        
        return $xml;
    }
    
    private function getStylesXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n" .
        '<fonts count="3">' .
        '<font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/><scheme val="minor"/></font>' .
        '<font><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/><scheme val="minor"/><b val="1"/></font>' .
        '<font><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/><scheme val="minor"/><b val="1"/></font>' .
        '</fonts>' . "\n" .
        '<fills count="3">' .
        '<fill><patternFill patternType="none"/></fill>' .
        '<fill><patternFill patternType="gray125"/></fill>' .
        '<fill><patternFill patternType="solid"><fgColor rgb="FFFF6B35"/><bgColor rgb="FFFF6B35"/></patternFill></fill>' .
        '</fills>' . "\n" .
        '<borders count="2">' .
        '<border><left/><right/><top/><bottom/><diagonal/></border>' .
        '<border><left style="thin"><color rgb="FFD3D3D3"/></left><right style="thin"><color rgb="FFD3D3D3"/></right><top style="thin"><color rgb="FFD3D3D3"/></top><bottom style="thin"><color rgb="FFD3D3D3"/></bottom><diagonal/></border>' .
        '</borders>' . "\n" .
        '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' . "\n" .
        '<cellXfs count="4">' .
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyBorder="0"/>' .
        '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyBorder="1" applyFill="1" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' .
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>' .
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' .
        '</cellXfs>' . "\n" .
        '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>' . "\n" .
        '<dxfs count="0"/>' . "\n" .
        '<tableStyles count="0" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleMedium4"/>' . "\n" .
        '</styleSheet>';
    }
    
    private function getContentTypesXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
        '<Default Extension="xml" ContentType="application/xml"/>' .
        '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
        '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
        '<Override PartName="/xl/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>' .
        '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
        '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>' .
        '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>' .
        '</Types>';
    }
    
    private function getRelsXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
        '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>' .
        '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>' .
        '</Relationships>';
    }
    
    private function getWorkbookXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
        '<fileVersion appName="xl" lastEdited="5" lowestEdited="5" rupBuild="19070"/>' .
        '<workbookPr defaultTheme="1"/>' .
        '<bookViews><workbookView xWindow="480" yWindow="60" windowWidth="25920" windowHeight="17640" tabRatio="500" activeTab="0"/></bookViews>' .
        '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>' .
        '<definedNames/>' .
        '<calcPr calcId="152049" concurrentCalc="0"/>' .
        '</workbook>';
    }
    
    private function getWorkbookRelsXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
        '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
        '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>' .
        '</Relationships>';
    }
    
    private function getCorePropsXml()
    {
        $date = date('Y-m-d\TH:i:s\Z');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/officeDocument/2006/metadata/core-properties"' .
        ' xmlns:dc="http://purl.org/dc/elements/1.1/"' .
        ' xmlns:dcterms="http://purl.org/dc/terms/"' .
        ' xmlns:dcmitype="http://purl.org/dc/dcmitype/"' .
        ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
        '<dc:creator>PT Enlulu</dc:creator>' .
        '<cp:lastModifiedBy>PT Enlulu</cp:lastModifiedBy>' .
        '<dcterms:created xsi:type="dcterms:W3CDTF">' . $date . '</dcterms:created>' .
        '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $date . '</dcterms:modified>' .
        '</cp:coreProperties>';
    }
    
    private function getAppPropsXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"' .
        ' xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">' .
        '<Application>Microsoft Excel</Application>' .
        '<DocSecurity>0</DocSecurity>' .
        '<ScaleCrop>false</ScaleCrop>' .
        '<HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>1</vt:i4></vt:variant></vt:vector></HeadingPairs>' .
        '<TitlesOfParts><vt:vector size="1" baseType="lpstr"><vt:lpstr>Sheet1</vt:lpstr></vt:vector></TitlesOfParts>' .
        '<Company>PT Enlulu</Company>' .
        '<LinksUpToDate>false</LinksUpToDate>' .
        '<SharedDoc>false</SharedDoc>' .
        '<HyperlinksChanged>false</HyperlinksChanged>' .
        '<AppVersion>16.0300</AppVersion>' .
        '</Properties>';
    }

    private function getThemeXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="Office Theme">' .
        '<a:themeElements>' .
        '<a:clrScheme name="Office">' .
        '<a:dk1><a:sysClr val="windowText" lastClr="000000"/></a:dk1>' .
        '<a:lt1><a:sysClr val="window" lastClr="FFFFFF"/></a:lt1>' .
        '<a:dk2><a:srgbClr val="1F497D"/></a:dk2>' .
        '<a:lt2><a:srgbClr val="EEECE1"/></a:lt2>' .
        '<a:accent1><a:srgbClr val="4F81BD"/></a:accent1>' .
        '<a:accent2><a:srgbClr val="C0504D"/></a:accent2>' .
        '<a:accent3><a:srgbClr val="9BBB59"/></a:accent3>' .
        '<a:accent4><a:srgbClr val="8064A2"/></a:accent4>' .
        '<a:accent5><a:srgbClr val="4BACC6"/></a:accent5>' .
        '<a:accent6><a:srgbClr val="F79646"/></a:accent6>' .
        '<a:hlink><a:srgbClr val="0000FF"/></a:hlink>' .
        '<a:folHlink><a:srgbClr val="800080"/></a:folHlink>' .
        '</a:clrScheme>' .
        '<a:fontScheme name="Office">' .
        '<a:majorFont><a:latin typeface="Calibri"/><a:ea typeface=""/><a:cs typeface=""/></a:majorFont>' .
        '<a:minorFont><a:latin typeface="Calibri"/><a:ea typeface=""/><a:cs typeface=""/></a:minorFont>' .
        '</a:fontScheme>' .
        '<a:fmtScheme name="Office">' .
        '<a:fillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill></a:fillStyleLst>' .
        '<a:lnStyleLst><a:ln w="9525" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill></a:ln></a:lnStyleLst>' .
        '<a:effectStyleLst><a:effectStyle><a:effectLst/></a:effectStyle></a:effectStyleLst>' .
        '<a:bgFillStyleLst><a:solidFill><a:schemeClr val="phClr"/></a:solidFill></a:bgFillStyleLst>' .
        '</a:fmtScheme>' .
        '</a:themeElements>' .
        '<a:objectDefaults/><a:extraClrSchemeLst/>' .
        '</a:theme>';
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        return view('employee.import');
    }

    /**
     * Validate import file dan return preview data
     */
    public function importValidate(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120'
            ]);

            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            
            if (in_array($ext, ['xlsx', 'xls'])) {
                $rows = $this->readExcelFile($file->getRealPath());
            } else {
                $handle = fopen($file->getRealPath(), 'r');
                $rows = [];
                while ($row = fgetcsv($handle)) {
                    $rows[] = $row;
                }
                fclose($handle);
            }
            
            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File kosong'
                ], 400);
            }

            // Auto-detect column mapping from header row
            $headers = array_map('strtoupper', array_map('trim', $rows[0]));
            $columnMap = $this->detectColumnMapping($headers);
            
            if (!$columnMap) {
                return response()->json([
                    'success' => false,
                    'error' => 'Format file tidak valid. Pastikan ada kolom NIK KTP dan NAMA KTP sesuai template.'
                ], 400);
            }

            $preview = [
                'created' => [],
                'updated' => [],
                'errors' => [],
                'created_count' => 0,
                'updated_count' => 0,
                'error_count' => 0,
                'total' => 0,
                'file_rows' => 0,
                'duplicate_count' => 0,
            ];

            $dataRows = array_slice($rows, 1);
            $existingEmployees = $this->existingEmployeesForImport($dataRows, $columnMap);
            $seenNiks = [];
            $row_num = 1;

            foreach ($dataRows as $row) {
                $row_num++;
                
                // Kolom NO boleh kosong; lewati hanya baris yang seluruh kolomnya kosong.
                if ($this->isImportRowEmpty($row)) {
                    continue;
                }

                $preview['file_rows']++;

                try {
                    [$nik, $nik_ktp] = $this->extractImportIdentity($row, $columnMap);
                    $this->ensureUniqueImportIdentity($nik, $row_num, $seenNiks);

                    $rawData = $this->buildRowData($nik, $nik_ktp, $row, $columnMap);
                    $dataToUpdate = $this->filterImportData($rawData);

                    // Check if employee exists
                    $existing = $existingEmployees->get($nik);
                    if ($existing) {
                        // Calculate changes
                        $changes = [];
                        foreach ($dataToUpdate as $field => $newVal) {
                            $dbVal = (string)($existing->getAttribute($field) ?? '');
                            $newValStr = (string)($newVal ?? '');
                            
                            if ($dbVal !== $newValStr) {
                                $changes[$field] = [
                                    'old' => $dbVal ?: '-',
                                    'new' => $newValStr ?: '-'
                                ];
                            }
                        }
                        
                        if (!empty($changes)) {
                            $changeDetails = [];
                            foreach ($changes as $field => $change) {
                                // Skip nik dan nama_lengkap (auto-fill dari nama_ktp)
                                if ($field !== 'nik' && $field !== 'nama_lengkap') {
                                    $changeDetails[$field] = $change;
                                }
                            }
                             
                            if (!empty($changeDetails)) {
                                $preview['updated_count']++;
                                if (count($preview['updated']) < self::IMPORT_PREVIEW_LIMIT) {
                                    $preview['updated'][] = [
                                        'nik' => $existing->nik,
                                        'nama' => $existing->nama_lengkap,
                                        'changes' => $changeDetails
                                    ];
                                }
                            }
                        }
                    } else {
                        // Create new employee
                        $preview['created_count']++;
                        if (count($preview['created']) < self::IMPORT_PREVIEW_LIMIT) {
                            $preview['created'][] = [
                                'nik' => $nik,
                                'nama' => $rawData['nama_lengkap'],
                                'nama_customer' => $rawData['nama_customer'],
                                'posisi' => $rawData['posisi'],
                                'type_lokasi' => $rawData['type_lokasi'],
                                'penempatan' => $rawData['penempatan'],
                                'area_kerja' => $rawData['area_kerja'],
                                'nama_bank' => $rawData['nama_bank'],
                                'no_rekening' => $rawData['no_rekening'],
                                'nama_di_rekening' => $rawData['nama_di_rekening'],
                                'status' => $rawData['status'] ?: 'Aktif',
                                'note1' => $rawData['note1']
                            ];
                        }
                    }
                     
                    $preview['total']++;
                } catch (\Exception $e) {
                    $preview['error_count']++;
                    if (str_starts_with($e->getMessage(), 'NIK KTP duplikat')) {
                        $preview['duplicate_count']++;
                    }

                    // Add row detail untuk context
                    if (count($preview['errors']) < self::IMPORT_PREVIEW_LIMIT) {
                        $nama_ktp = trim($row[$columnMap['nama_ktp'] ?? 0] ?? '') ?: '-';
                        $posisi = trim($row[$columnMap['posisi'] ?? 0] ?? '') ?: '-';
                        $preview['errors'][] = "Baris $row_num (Nama: $nama_ktp, Posisi: $posisi): " . $e->getMessage();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'columnMapping' => $columnMap,
                'headers' => $headers
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validasi gagal: ' . implode(', ', array_merge(...array_values($e->errors())))
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal membaca file: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Import employees dari CSV/Excel dengan flexible column mapping
     */
    public function import(Request $request)
    {
        $transactionStarted = false;

        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120'
            ]);

            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            
            if (in_array($ext, ['xlsx', 'xls'])) {
                $rows = $this->readExcelFile($file->getRealPath());
            } else {
                $handle = fopen($file->getRealPath(), 'r');
                $rows = [];
                while ($row = fgetcsv($handle)) {
                    $rows[] = $row;
                }
                fclose($handle);
            }
            
            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File kosong'
                ], 400);
            }

            // Auto-detect column mapping from header row
            $headers = array_map('strtoupper', array_map('trim', $rows[0]));
            $columnMap = $this->detectColumnMapping($headers);
            
            if (!$columnMap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format file tidak valid. Pastikan ada kolom NIK KTP dan NAMA KTP sesuai template.'
                ], 400);
            }

            $imported = 0;
            $created = [];
            $updated = [];
            $errors = [];
            $fileRows = 0;
            $duplicateCount = 0;
            $dataRows = array_slice($rows, 1);
            $existingEmployees = $this->existingEmployeesForImport($dataRows, $columnMap);
            $seenNiks = [];
            $row_num = 1;

            DB::beginTransaction();
            $transactionStarted = true;

            foreach ($dataRows as $row) {
                $row_num++;
                
                // Kolom NO boleh kosong; lewati hanya baris yang seluruh kolomnya kosong.
                if ($this->isImportRowEmpty($row)) {
                    continue;
                }

                $fileRows++;

                try {
                    [$nik, $nik_ktp] = $this->extractImportIdentity($row, $columnMap);
                    $this->ensureUniqueImportIdentity($nik, $row_num, $seenNiks);

                    $rawData = $this->buildRowData($nik, $nik_ktp, $row, $columnMap);
                    $dataToUpdate = $this->filterImportData($rawData);

                    // Check if employee exists by NIK
                    $existing = $existingEmployees->get($nik);
                    if ($existing) {
                        // Calculate changes
                        $changes = [];
                        foreach ($dataToUpdate as $field => $newVal) {
                            $dbVal = (string)($existing->getAttribute($field) ?? '');
                            $newValStr = (string)($newVal ?? '');
                            
                            if ($dbVal !== $newValStr) {
                                $changes[$field] = [
                                    'old' => $dbVal ?: '-',
                                    'new' => $newValStr ?: '-'
                                ];
                            }
                        }
                        
                        // Update if there are changes
                        if (!empty($changes)) {
                            $existing->update($dataToUpdate);
                            
                            $changeDetails = [];
                            foreach ($changes as $field => $change) {
                                // Skip nik dan nama_lengkap (auto-fill dari nama_ktp)
                                if ($field !== 'nik' && $field !== 'nama_lengkap') {
                                    $changeDetails[$field] = $change;
                                }
                            }
                            
                            if (!empty($changeDetails)) {
                                $updated[] = [
                                    'nik' => $existing->nik,
                                    'nama' => $existing->nama_lengkap,
                                    'changes' => $changeDetails
                                ];
                            }
                        }
                    } else {
                        // Create new employee
                        $newEmployee = Employee::create($dataToUpdate);
                        $existingEmployees->put($nik, $newEmployee);
                        
                        $created[] = [
                            'nik' => $newEmployee->nik,
                            'nama' => $newEmployee->nama_lengkap,
                            'nama_customer' => $newEmployee->nama_customer,
                            'posisi' => $newEmployee->posisi,
                            'type_lokasi' => $newEmployee->type_lokasi,
                            'penempatan' => $newEmployee->penempatan,
                            'area_kerja' => $newEmployee->area_kerja,
                            'nama_bank' => $newEmployee->nama_bank,
                            'no_rekening' => $newEmployee->no_rekening,
                            'nama_di_rekening' => $newEmployee->nama_di_rekening,
                            'status' => $newEmployee->status,
                            'note1' => $newEmployee->note1
                        ];
                    }
                    
                    $imported++;
                } catch (\Exception $e) {
                    if (str_starts_with($e->getMessage(), 'NIK KTP duplikat')) {
                        $duplicateCount++;
                    }

                    // Add row detail untuk context
                    $nama_ktp = trim($row[$columnMap['nama_ktp'] ?? 0] ?? '') ?: '-';
                    $posisi = trim($row[$columnMap['posisi'] ?? 0] ?? '') ?: '-';
                    $errors[] = "Baris $row_num (Nama: $nama_ktp, Posisi: $posisi): " . $e->getMessage();
                }
            }

            // Log and return response
            if ($imported > 0 || !empty($created) || !empty($updated)) {
                ActivityLog::log(
                    'import',
                    'Employee',
                    'batch',
                    "Import $imported data karyawan (Baru: " . count($created) . ", Update: " . count($updated) . ")",
                    [],  // old_values - empty array untuk import
                    [
                        'created' => $created,      // Pass full array with nik & nama
                        'updated' => $updated,      // Pass full array with changes
                        'errors' => $errors         // Pass full error messages
                    ]
                );

                DB::commit();
                $transactionStarted = false;

                return response()->json([
                    'success' => true,
                    'message' => "Selesai memproses $fileRows baris: $imported berhasil dan " . count($errors) . " gagal",
                    'file_rows' => $fileRows,
                    'imported_count' => $imported,
                    'created_count' => count($created),
                    'updated_count' => count($updated),
                    'error_count' => count($errors),
                    'duplicate_count' => $duplicateCount,
                    'error_samples' => array_slice($errors, 0, 20),
                    'redirect' => route('employee.index')
                ], 200);
            }

            DB::rollBack();
            $transactionStarted = false;

            return response()->json([
                'success' => false,
                'message' => 'Gagal import file: Tidak ada data yang berhasil diproses'
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($transactionStarted) {
                DB::rollBack();
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_merge(...array_values($e->errors())))
            ], 422);
        } catch (\Exception $e) {
            if ($transactionStarted) {
                DB::rollBack();
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal import file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-detect column mapping from headers dengan priority-based matching
     * Setiap field memiliki prioritas match: lebih spesifik dulu, lebih generik belakangan
     */
    private function detectColumnMapping(array $headers): ?array
    {
        // Bangun mapping kosong dari daftar field importable + nik_ktp
        $mapping = ['nik_ktp' => null];
        foreach ($this->importableFields() as $field) {
            $mapping[$field] = null;
        }

        // Aliases dengan PRIORITY ORDER - lebih spesifik dulu, generik belakangan
        $aliases = [
            'nik_ktp' => ['NIK KTP', 'NO KTP', 'NIK'],
            'nama_ktp' => ['NAMA SESUAI KTP', 'NAMA KTP', 'NAMA'],
            'nik_enlulu' => ['NIK ENLULU', 'NIK SEMENTARA ENLULU', 'ENLULU NIK'],
            'nik_os' => ['NIK OS', 'NIK OUTSOURCING', 'NIK CLIENT'],
            'nama_customer' => ['NAMA CUSTOMER', 'KLIEN', 'CLIENT', 'CUSTOMER'],
            'posisi' => ['JABATAN', 'POSISI'],
            'penempatan' => ['LOKASI KERJA', 'PENEMPATAN', 'LOKASI'],
            'type_lokasi' => ['TYPE LOKASI', 'TIPE LOKASI'],
            'area_kerja' => ['AREA KERJA', 'AREA'],
            'no_rekening' => ['NO REKENING', 'NO. REKENING', 'NOREK'],
            'nama_bank' => ['NAMA BANK'],
            'nama_di_rekening' => ['NAMA PEMILIK REKENING', 'NAMA REKENING', 'NAMA DI REKENING', 'PEMILIK REKENING'],
            'status' => ['STATUS KERJA', 'STATUS'],
            'note1' => ['KETERANGAN LAIN-LAIN', 'KETERANGAN LAIN LAIN', 'NOTE', 'NOTE1', 'CATATAN'],

            // A. Biodata
            'tempat_lahir' => ['TEMPAT LAHIR'],
            'tanggal_lahir' => ['TANGGAL LAHIR', 'TGL LAHIR'],
            'jenis_kelamin' => ['JENIS KELAMIN', 'GENDER', 'KELAMIN'],
            'agama' => ['AGAMA'],
            'pendidikan' => ['PENDIDIKAN'],
            'status_pernikahan' => ['STATUS PERNIKAHAN', 'STATUS NIKAH', 'PERNIKAHAN'],
            'jumlah_anak' => ['JUMLAH ANAK', 'ANAK'],
            'alamat' => ['ALAMAT TINGGAL', 'ALAMAT'],
            'kelurahan' => ['KELURAHAN'],
            'kecamatan' => ['KECAMATAN'],
            'kota' => ['KOTA/KABUPATEN', 'KOTA / KABUPATEN', 'KOTA', 'KABUPATEN'],
            'propinsi' => ['PROPINSI', 'PROVINSI'],
            'status_tempat_tinggal' => ['STATUS TEMPAT TINGGAL'],
            'no_hp' => ['NO HP', 'NO. HP', 'NO HANDPHONE', 'HANDPHONE', 'HP'],
            'no_kk' => ['NO KK', 'NO. KK', 'NOMOR KK', 'KK'],
            'email' => ['E-MAIL', 'EMAIL'],
            'no_bpjs_tk' => ['NO BPJS KETENAGAKERJAAN', 'NO. BPJS KETENAGAKERJAAN', 'BPJS KETENAGAKERJAAN', 'BPJS TK'],
            'no_bpjs_kesehatan' => ['NO BPJS KESEHATAN', 'NO. BPJS KESEHATAN', 'BPJS KESEHATAN'],
            'keterangan_biodata' => ['KETERANGAN LAIN BIODATA', 'KETERANGAN BIODATA'],

            // B. Emergency Contact
            'ec_nama' => ['EMERGENCY CONTACT NAMA', 'KONTAK DARURAT NAMA', 'NAMA DARURAT', 'EC NAMA'],
            'ec_alamat' => ['EMERGENCY CONTACT ALAMAT', 'ALAMAT DARURAT', 'EC ALAMAT', 'EC TEMPAT TINGGAL'],
            'ec_no_hp' => ['EMERGENCY CONTACT NO HP', 'NO HP DARURAT', 'HP DARURAT', 'EC NO HP', 'EC HP'],
            'ec_hubungan' => ['HUBUNGAN', 'EC HUBUNGAN'],

            // E. Penempatan Kerja
            'tanggal_masuk' => ['TANGGAL MASUK', 'TGL MASUK'],
            'tanggal_keluar' => ['TANGGAL KELUAR', 'TGL KELUAR'],
            'tanggal_perpanjangan_terakhir' => ['TANGGAL PERPANJANGAN TERAKHIR', 'TGL PERPANJANGAN'],
            'keterangan_perpanjangan' => ['KETERANGAN PERPANJANGAN'],
            'no_pks_masuk' => ['NO PKS MASUK', 'NO. PKS MASUK'],
            'no_pks_perpanjangan' => ['NO PKS PERPANJANGAN', 'NO. PKS PERPANJANGAN'],
            'nama_perekrut' => ['NAMA PEREKRUT', 'PEREKRUT', 'REFERENSI'],
        ];

        // Track kolom mana yang sudah dipake (untuk tidak duplikat)
        $usedIndices = [];

        // Match setiap field dengan priority order - ambil match pertama yang belum dipake
        foreach ($aliases as $field => $possibleHeaders) {
            foreach ($possibleHeaders as $headerName) {
                // Cari header yang match dengan nama ini
                foreach ($headers as $idx => $header) {
                    if ($header === $headerName && !in_array($idx, $usedIndices)) {
                        // Found: header match dan belum dipake
                        $mapping[$field] = $idx;
                        $usedIndices[] = $idx;
                        break 2;  // Exit dua loop setelah ketemu
                    }
                }
            }
        }

        // Identitas minimum yang wajib tersedia pada template import.
        if ($mapping['nik_ktp'] === null || $mapping['nama_ktp'] === null) {
            return null;
        }

        return $mapping;
    }

    /**
     * Daftar field yang dapat diimpor/diekspor (urutan tampilan), selain
     * nik/nik_ktp/nama_lengkap yang ditangani khusus.
     */
    private function importableFields(): array
    {
        return [
            'nama_ktp', 'nik_enlulu', 'nik_os', 'nama_customer', 'posisi', 'penempatan',
            'type_lokasi', 'area_kerja', 'no_rekening', 'nama_bank', 'nama_di_rekening',
            'status', 'note1',
            // A. Biodata
            'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama', 'pendidikan',
            'status_pernikahan', 'jumlah_anak', 'alamat', 'kelurahan', 'kecamatan',
            'kota', 'propinsi', 'status_tempat_tinggal', 'no_hp', 'no_kk', 'email',
            'no_bpjs_tk', 'no_bpjs_kesehatan', 'keterangan_biodata',
            // B. Emergency Contact
            'ec_nama', 'ec_alamat', 'ec_no_hp', 'ec_hubungan',
            // E. Penempatan Kerja
            'tanggal_masuk', 'tanggal_keluar', 'tanggal_perpanjangan_terakhir',
            'keterangan_perpanjangan', 'no_pks_masuk', 'no_pks_perpanjangan', 'nama_perekrut',
        ];
    }

    /**
     * Bangun array data baris dari row Excel/CSV sesuai column mapping.
     */
    private function buildRowData(string $nik, string $nikKtp, array $row, array $columnMap): array
    {
        $data = [
            'nik' => $nik,
            'nik_ktp' => $nikKtp,
            'nama_lengkap' => $columnMap['nama_ktp'] !== null ? trim($row[$columnMap['nama_ktp']] ?? '') : '',
        ];

        foreach ($this->importableFields() as $field) {
            $idx = $columnMap[$field] ?? null;
            $value = $idx !== null ? trim($row[$idx] ?? '') : '';
            if ($field === 'posisi') {
                $value = $this->normalizePosition($value);
            }
            if ($field === 'jenis_kelamin') {
                $value = $this->normalizeGender($value);
            }
            if ($field === 'status' && $value !== '') {
                $normalizedStatus = Employee::normalizeStatusValue($value);
                if ($normalizedStatus === null) {
                    throw new \InvalidArgumentException("Status kerja tidak valid: '$value'");
                }
                $value = $normalizedStatus;
            }
            if (in_array($field, $this->importDateFields(), true)) {
                $value = $this->normalizeImportDate($value, $field);
            }
            $data[$field] = $value;
        }

        return $data;
    }

    /**
     * Ambil dan validasi identitas minimum untuk setiap baris import.
     */
    private function extractImportIdentity(array $row, array $columnMap): array
    {
        $nikKtp = trim($row[$columnMap['nik_ktp']] ?? '');
        $namaKtp = trim($row[$columnMap['nama_ktp']] ?? '');

        if (!preg_match('/^[0-9]{16}$/', $nikKtp)) {
            throw new \InvalidArgumentException('NIK KTP harus 16 angka (NIK: '.$nikKtp.')');
        }

        if ($namaKtp === '') {
            throw new \InvalidArgumentException('Nama sesuai KTP wajib diisi');
        }

        return [$nikKtp, $nikKtp];
    }

    /**
     * Pastikan satu NIK hanya diproses sekali dalam satu file import.
     */
    private function ensureUniqueImportIdentity(string $nik, int $rowNumber, array &$seenNiks): void
    {
        if (isset($seenNiks[$nik])) {
            throw new \InvalidArgumentException(
                "NIK KTP duplikat dengan baris {$seenNiks[$nik]} (NIK: $nik)"
            );
        }

        $seenNiks[$nik] = $rowNumber;
    }

    /**
     * Ambil seluruh employee existing dalam satu query untuk menghindari query per baris.
     */
    private function existingEmployeesForImport(array $rows, array $columnMap)
    {
        $niks = [];

        foreach ($rows as $row) {
            if ($this->isImportRowEmpty($row)) {
                continue;
            }

            try {
                [$nik] = $this->extractImportIdentity($row, $columnMap);
                $niks[$nik] = true;
            } catch (\Exception) {
                // Error identitas tetap dilaporkan oleh loop validasi/import utama.
            }
        }

        if (empty($niks)) {
            return collect();
        }

        return Employee::query()
            ->whereIn('nik', array_keys($niks))
            ->get()
            ->keyBy('nik');
    }

    /**
     * Kolom kosong tidak boleh menghapus data lama saat import pembaruan.
     */
    private function filterImportData(array $rawData): array
    {
        return array_filter(
            $rawData,
            fn ($value, $field) => $value !== '' || in_array($field, ['nik', 'nik_ktp'], true),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function isImportRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeGender(?string $gender): string
    {
        $value = trim((string) $gender);
        $key = preg_replace('/[^A-Z]/', '', strtoupper($value));

        return match ($key) {
            'LAKILAKI', 'PRIA' => 'Pria',
            'PEREMPUAN', 'WANITA' => 'Wanita',
            default => $value,
        };
    }

    private function normalizeRequestStatus(Request $request): void
    {
        if (!$request->exists('status') || trim((string) $request->input('status')) === '') {
            return;
        }

        $normalized = Employee::normalizeStatusValue($request->input('status'));
        if ($normalized !== null) {
            $request->merge(['status' => $normalized]);
        }
    }

    private function normalizeCustomerField(Request $request): void
    {
        if (!$request->filled('nama_customer') && $request->filled('klien')) {
            $request->merge(['nama_customer' => $request->input('klien')]);
        }
    }

    private function importDateFields(): array
    {
        return [
            'tanggal_lahir',
            'tanggal_masuk',
            'tanggal_keluar',
            'tanggal_perpanjangan_terakhir',
        ];
    }

    /**
     * Ubah tanggal dari serial Excel, format angka, atau nama bulan Indonesia.
     */
    private function normalizeImportDate(string $value, string $field): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                throw new \InvalidArgumentException(
                    "Format tanggal tidak valid pada {$this->importDateLabel($field)}: '$value'"
                );
            }
        }

        $normalized = strtoupper(preg_replace('/\s+/', ' ', $value) ?? $value);
        $normalized = strtr($normalized, [
            'JANUARI' => 'JANUARY',
            'FEBRUARI' => 'FEBRUARY',
            'MARET' => 'MARCH',
            'MEI' => 'MAY',
            'JUNI' => 'JUNE',
            'JULI' => 'JULY',
            'AGUSTUS' => 'AUGUST',
            'OKTOBER' => 'OCTOBER',
            'DESEMBER' => 'DECEMBER',
        ]);

        foreach ([
            '!d F Y', '!j F Y', '!d M Y', '!j M Y',
            '!d-m-Y', '!j-n-Y', '!d/m/Y', '!j/n/Y',
            '!Y-m-d', '!Y/m/d',
            '!d-m-y', '!j-n-y', '!d/m/y', '!j/n/y',
            '!Y-m-d H:i:s', '!d-m-Y H:i:s', '!d/m/Y H:i:s',
        ] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $normalized);
            $errors = \DateTimeImmutable::getLastErrors();
            $hasErrors = is_array($errors)
                && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);

            if ($date instanceof \DateTimeImmutable && !$hasErrors) {
                return $date->format('Y-m-d');
            }
        }

        throw new \InvalidArgumentException(
            "Format tanggal tidak dikenali pada {$this->importDateLabel($field)}: '$value'"
        );
    }

    private function importDateLabel(string $field): string
    {
        return match ($field) {
            'tanggal_lahir' => 'TANGGAL LAHIR',
            'tanggal_masuk' => 'TANGGAL MASUK',
            'tanggal_keluar' => 'TANGGAL KELUAR',
            'tanggal_perpanjangan_terakhir' => 'TANGGAL PERPANJANGAN TERAKHIR',
            default => strtoupper(str_replace('_', ' ', $field)),
        };
    }

    /**
     * Definisi kolom export/template: [Header, field].
     */
    private function exportColumns(): array
    {
        return [
            // Identitas utama
            ['NIK KTP', 'nik_ktp'],
            ['NAMA KTP', 'nama_ktp'],
            ['NIK ENLULU', 'nik_enlulu'],
            ['NIK OS', 'nik_os'],

            // A. Biodata
            ['TEMPAT LAHIR', 'tempat_lahir'],
            ['TANGGAL LAHIR', 'tanggal_lahir'],
            ['JENIS KELAMIN', 'jenis_kelamin'],
            ['AGAMA', 'agama'],
            ['PENDIDIKAN', 'pendidikan'],
            ['STATUS PERNIKAHAN', 'status_pernikahan'],
            ['JUMLAH ANAK', 'jumlah_anak'],
            ['ALAMAT TINGGAL', 'alamat'],
            ['KELURAHAN', 'kelurahan'],
            ['KECAMATAN', 'kecamatan'],
            ['KOTA/KABUPATEN', 'kota'],
            ['PROPINSI', 'propinsi'],
            ['STATUS TEMPAT TINGGAL', 'status_tempat_tinggal'],
            ['NO HP', 'no_hp'],
            ['NO KK', 'no_kk'],
            ['E-MAIL', 'email'],
            ['NO BPJS KETENAGAKERJAAN', 'no_bpjs_tk'],
            ['NO BPJS KESEHATAN', 'no_bpjs_kesehatan'],
            ['KETERANGAN LAIN BIODATA', 'keterangan_biodata'],

            // B. Emergency Contact
            ['EMERGENCY CONTACT NAMA', 'ec_nama'],
            ['EMERGENCY CONTACT ALAMAT', 'ec_alamat'],
            ['EMERGENCY CONTACT NO HP', 'ec_no_hp'],
            ['HUBUNGAN', 'ec_hubungan'],

            // C. Banking
            ['NAMA BANK', 'nama_bank'],
            ['NO REKENING', 'no_rekening'],
            ['NAMA PEMILIK REKENING', 'nama_di_rekening'],

            // E. Penempatan Kerja
            ['KLIEN', 'nama_customer'],
            ['JABATAN', 'posisi'],
            ['TYPE LOKASI', 'type_lokasi'],
            ['LOKASI KERJA', 'penempatan'],
            ['AREA KERJA', 'area_kerja'],
            ['TANGGAL MASUK', 'tanggal_masuk'],
            ['STATUS KERJA', 'status'],
            ['TANGGAL KELUAR', 'tanggal_keluar'],
            ['TANGGAL PERPANJANGAN TERAKHIR', 'tanggal_perpanjangan_terakhir'],
            ['KETERANGAN PERPANJANGAN', 'keterangan_perpanjangan'],
            ['NO PKS MASUK', 'no_pks_masuk'],
            ['NO PKS PERPANJANGAN', 'no_pks_perpanjangan'],
            ['KETERANGAN LAIN-LAIN', 'note1'],

            // Perekrut
            ['NAMA PEREKRUT', 'nama_perekrut'],
        ];
    }
    
    private function readExcelFile(string $filePath): array
    {
        $rows = [];
        $zip = new \ZipArchive();
        
        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception("Tidak bisa membuka file Excel");
        }
        
        // Read the main worksheet XML
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new \Exception("File Excel tidak valid");
        }
        
        // Parse XML
        $dom = new \DOMDocument();
        @$dom->loadXML($sheetXml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('ss', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        
        // Get shared strings for cell value references
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $sharedStrings = [];
        if ($sharedStringsXml) {
            $stringsDom = new \DOMDocument();
            @$stringsDom->loadXML($sharedStringsXml);
            $stringsXpath = new \DOMXPath($stringsDom);
            $stringsXpath->registerNamespace('ss', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            
            $stringCells = $stringsXpath->query('//ss:si');
            foreach ($stringCells as $si) {
                if ($si instanceof \DOMElement) {
                    $t = $si->getElementsByTagName('t');
                    if ($t->length > 0) {
                        $sharedStrings[] = $t->item(0)->nodeValue;
                    }
                }
            }
        }
        
        $zip->close();
        
        // Extract cell values - INCLUDE HEADER ROW & ALL COLUMNS
        $sheetRows = $xpath->query('//ss:row');
        
        foreach ($sheetRows as $sheetRow) {
            $row = [];
            if ($sheetRow instanceof \DOMElement) {
                $cells = $sheetRow->getElementsByTagName('c');
            } else {
                $cells = new \DOMNodeList();
            }
            
            // Read ALL columns based on cell reference so empty cells do not shift mapping.
            for ($i = 0; $i < $cells->length; $i++) {
                $value = '';
                $cell = $cells->item($i);
                if ($cell instanceof \DOMElement) {
                    $cellReference = $cell->getAttribute('r');
                    $columnLetters = preg_replace('/\d+/', '', $cellReference);
                    $columnIndex = $columnLetters
                        ? Coordinate::columnIndexFromString($columnLetters) - 1
                        : $i;
                    $type = $cell->getAttribute('t');
                    $v = $cell->getElementsByTagName('v');
                    
                    if ($v->length > 0) {
                        $cellValue = $v->item(0)->nodeValue;
                        
                        if ($type === 's') {
                            // String reference
                            $value = isset($sharedStrings[$cellValue]) ? $sharedStrings[$cellValue] : '';
                        } else {
                            $value = $cellValue;
                        }
                    } elseif ($type === 'inlineStr') {
                        $inlineStrings = $cell->getElementsByTagName('t');
                        if ($inlineStrings->length > 0) {
                            $value = $inlineStrings->item(0)->nodeValue;
                        }
                    }
                    $row[$columnIndex] = $value;
                }
            }

            if (!empty($row)) {
                ksort($row);
                $maxColumn = max(array_keys($row));
                $row = array_replace(array_fill(0, $maxColumn + 1, ''), $row);
            }
            
            // Kolom pertama (NO) boleh kosong selama ada data pada kolom lain.
            if (count($rows) === 0 || !$this->isImportRowEmpty($row)) {
                $rows[] = $row;
            }
        }
        
        return $rows;
    }

    /**
     * Export employees ke Excel dengan filter yang sesuai
     */
    public function export(Request $request)
    {
        $query = Employee::query();

        // Access control: samakan dengan daftar karyawan.
        /** @var User|null $user */
        $user = $this->currentUser();
        if ($user && $user->isAdminPic()) {
            $managedPositions = $user->getManagedPositions();
            $query->whereIn('posisi', $managedPositions);
        }
        
        // Filter berdasarkan status jika ada
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Apply filters yang sama seperti di index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('nik_ktp', 'like', "%{$search}%")
                    ->orWhere('nama_ktp', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('posisi', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('penempatan')) {
            $query->where('penempatan', 'like', "%{$request->penempatan}%");
        }
        
        if ($request->filled('posisi')) {
            $query->where('posisi', 'like', "%{$request->posisi}%");
        }

        $employees = $query->orderBy('nik')->get();
        $columns = $this->exportColumns();
        $headers = array_merge(['NO'], array_map(fn ($c) => $c[0], $columns));

        $data = $employees->map(function ($emp, $index) use ($columns) {
            $row = [$index + 1];
            foreach ($columns as $c) {
                $val = $emp->getAttribute($c[1]);
                if ($val instanceof \Carbon\Carbon) {
                    $val = $val->format('Y-m-d');
                }
                $row[] = ($val === null) ? '' : (string) $val;
            }
            return $row;
        })->toArray();
        
        // Set filename berdasarkan status filter
        $statusLabel = '';
        if ($request->filled('status')) {
            $statusLabel = '_' . ucfirst($request->status);
        }
        $filename = 'Data_Karyawan' . $statusLabel . '_' . date('Y-m-d_His') . '.xlsx';
        
        return response()->streamDownload(function () use ($headers, $data) {
            $this->generateExcelExport($headers, $data);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Generate Excel file untuk export
     */
    private function generateExcelExport(array $headers, array $data): void
    {
        $this->renderSpreadsheet($headers, $data);
    }
    
    /**
     * Generate sheet XML for export dengan formatting yang lebih baik
     */
    private function getSheetXmlForExport(array $headers, array $data): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        
        // Column widths
        $xml .= '<cols>' . "\n";
        $widths = [16, 25, 18, 18, 18, 18, 25, 14]; // Updated untuk 8 columns (+ Status)
        foreach ($widths as $index => $width) {
            $col = chr(65 + $index);
            $xml .= '<col min="' . ($index + 1) . '" max="' . ($index + 1) . '" width="' . $width . '" customWidth="1"/>' . "\n";
        }
        $xml .= '</cols>' . "\n";
        
        $xml .= '<sheetData>' . "\n";
        
        // Header row dengan formatting
        $xml .= '<row r="1" ht="25" customHeight="1">' . "\n";
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);
            // Style "1" adalah untuk header (orange background)
            $xml .= '<c r="' . $col . '1" s="1" t="inlineStr"><is><t>' . htmlspecialchars($header) . '</t></is></c>' . "\n";
        }
        $xml .= '</row>' . "\n";
        
        // Data rows
        foreach ($data as $rowIndex => $row) {
            $xml .= '<row r="' . ($rowIndex + 2) . '" ht="20" customHeight="1">' . "\n";
            foreach ($row as $colIndex => $value) {
                $col = chr(65 + $colIndex);
                // Determine cell type dan style
                $style = '2'; // Default style dengan border
                
                // Column 0 (NIK) - align center
                if ($colIndex === 0) {
                    $style = '3';
                }
                
                $xml .= '<c r="' . $col . ($rowIndex + 2) . '" s="' . $style . '" t="inlineStr"><is><t>' . htmlspecialchars($value) . '</t></is></c>' . "\n";
            }
            $xml .= '</row>' . "\n";
        }
        
        $xml .= '</sheetData>' . "\n";
        $xml .= '<pageMargins left="0.75" top="1" right="0.75" bottom="1" header="0.5" footer="0.5"/>' . "\n";
        $xml .= '<pageSetup paperSize="9" orientation="portrait"/>' . "\n";
        $xml .= '</worksheet>' . "\n";
        
        return $xml;
    }
    
    /**
     * Generate optimized styles XML untuk export
     */
    private function getStylesXmlForExport()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
        '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n" .
        '<fonts count="3">' .
        '<font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/><scheme val="minor"/></font>' .
        '<font><sz val="11"/><bold val="1"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>' .
        '<font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/><scheme val="minor"/></font>' .
        '</fonts>' . "\n" .
        '<fills count="3">' .
        '<fill><patternFill patternType="none"/></fill>' .
        '<fill><patternFill patternType="gray125"/></fill>' .
        '<fill><patternFill patternType="solid"><fgColor rgb="FFFF6B35"/><bgColor rgb="FFFF6B35"/></patternFill></fill>' .
        '</fills>' . "\n" .
        '<borders count="2">' .
        '<border><left/><right/><top/><bottom/><diagonal/></border>' .
        '<border><left style="thin"><color rgb="FFD3D3D3"/></left><right style="thin"><color rgb="FFD3D3D3"/></right><top style="thin"><color rgb="FFD3D3D3"/></top><bottom style="thin"><color rgb="FFD3D3D3"/></bottom><diagonal/></border>' .
        '</borders>' . "\n" .
        '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' . "\n" .
        '<cellXfs count="4">' .
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyBorder="0"/>' .
        '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyBorder="1" applyFill="1" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' .
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>' .
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' .
        '</cellXfs>' . "\n" .
        '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>' . "\n" .
        '<dxfs count="0"/>' . "\n" .
        '<tableStyles count="0" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleMedium4"/>' . "\n" .
        '</styleSheet>';
    }

    /**
     * Show report of active and resigned employees
     */
    public function report(Request $request)
    {
        // Get counts by status
        $totalKaryawan = Employee::count();
        $totalAktif    = Employee::where('status', 'Aktif')->count();
        $totalTraining = Employee::where('status', 'Training')->count();
        $totalResign   = Employee::where('status', 'Resign')->count();
        $totalFraud    = Employee::where('status', 'Fraud')->count();
        $totalCancel   = Employee::where('status', 'Cancel')->count();

        // Get detailed data
        $statusFilter = $request->get('status', 'Aktif');
        $query = Employee::query();
        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%$search%")
                  ->orWhere('nama_lengkap', 'like', "%$search%")
                  ->orWhere('posisi', 'like', "%$search%");
            });
        }

        // Apply location filter
        if ($request->filled('penempatan')) {
            $query->where('penempatan', 'like', "%{$request->penempatan}%");
        }

        // Apply position filter
        if ($request->filled('posisi')) {
            $query->where('posisi', 'like', "%{$request->posisi}%");
        }

        $employees = $query->orderBy('nik')->paginate(20);
        $penempatan = Employee::distinct()->pluck('penempatan')->filter();
        $posisi = Employee::distinct()->pluck('posisi')->filter();

        // Get breakdown by location and position
        $locationQuery = Employee::query();
        $positionQuery = Employee::query();
        if ($statusFilter !== '') {
            $locationQuery->where('status', $statusFilter);
            $positionQuery->where('status', $statusFilter);
        }
        $byLocation = $locationQuery->groupBy('penempatan')
                                    ->selectRaw('penempatan, COUNT(*) as count')
                                    ->get();
        $byPosition = $positionQuery->groupBy('posisi')
                                    ->selectRaw('posisi, COUNT(*) as count')
                                    ->get();

        return view('employee.report', compact(
            'employees',
            'totalKaryawan',
            'totalAktif',
            'totalTraining',
            'totalResign',
            'totalFraud',
            'totalCancel',
            'statusFilter',
            'byLocation',
            'byPosition',
            'penempatan',
            'posisi'
        ));
    }
}
