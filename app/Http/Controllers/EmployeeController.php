<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::query();
        
        // Access control: Filter by user role
        /** @var User|null $user */
        $user = auth()->user();
        if ($user && $user->isAdminPic()) {
            // ADMIN/PIC hanya bisa lihat posisi yang di-handle
            $adminPicDepartments = config('positions.admin_pic_departments', []);
            $managedPositions = $adminPicDepartments[$user->handled_position] ?? [];
            
            if (!empty($managedPositions)) {
                $query->whereIn('posisi', $managedPositions);
            }
        }
        // Super Admin dan role lain bisa lihat semua
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nik', 'like', "%$search%")
                  ->orWhere('nama_lengkap', 'like', "%$search%")
                  ->orWhere('posisi', 'like', "%$search%");
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employee.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|unique:employees,nik|regex:/^[0-9]{16}$/|digits:16',
            'nama_ktp' => 'required|string|max:100',
            'kode_vendor' => 'nullable|string|max:50',
            'posisi' => 'nullable|string|max:100',
            'penempatan' => 'nullable|string|max:100',
            'type_lokasi' => 'nullable|string|max:50',
            'area_kerja' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:30',
            'nama_bank' => 'nullable|string|max:50',
            'nama_di_rekening' => 'nullable|string|max:100',
            'status' => 'nullable|in:Aktif,Training,Resign',
            'note1' => 'nullable|string|max:500',
        ], [
            'nik.required' => 'NIK harus diisi',
            'nik.unique' => 'NIK sudah terdaftar dalam sistem',
            'nik.regex' => 'NIK harus terdiri dari 16 angka',
            'nik.digits' => 'NIK harus terdiri dari 16 angka',
            'nama_ktp.required' => 'Nama harus diisi',
            'status.in' => 'Status tidak valid',
        ]);

        // Auto-fill nik_ktp dan nama_lengkap with nik and nama_ktp respectively
        $validated['nik_ktp'] = $validated['nik'];
        $validated['nama_lengkap'] = $validated['nama_ktp'];

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
        $user = auth()->user();
        if ($user && $user->isAdminPic()) {
            if (!$user->canAccessPosition($employee->posisi)) {
                abort(403, 'Anda tidak memiliki akses ke data karyawan ini.');
            }
        }
        
        return view('employee.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        // Access control: Check if user can edit this employee
        /** @var User|null $user */
        $user = auth()->user();
        if ($user && $user->isAdminPic()) {
            if (!$user->canAccessPosition($employee->posisi)) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit data karyawan ini.');
            }
        }
        
        return view('employee.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        // Access control: Check if user can update this employee
        /** @var User|null $user */
        $user = auth()->user();
        if ($user && $user->isAdminPic()) {
            if (!$user->canAccessPosition($employee->posisi)) {
                abort(403, 'Anda tidak memiliki akses untuk mengubah data karyawan ini.');
            }
        }
        
        $validated = $request->validate([
            'nik' => 'required|unique:employees,nik,' . $employee->nik . ',nik|regex:/^[0-9]{16}$/|digits:16',
            'nama_ktp' => 'required|string|max:100',
            'kode_vendor' => 'nullable|string|max:50',
            'posisi' => 'nullable|string|max:100',
            'penempatan' => 'nullable|string|max:100',
            'type_lokasi' => 'nullable|string|max:50',
            'area_kerja' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:30',
            'nama_bank' => 'nullable|string|max:50',
            'nama_di_rekening' => 'nullable|string|max:100',
            'status' => 'nullable|in:Aktif,Training,Resign',
            'note1' => 'nullable|string|max:500',
        ], [
            'nik.required' => 'NIK harus diisi',
            'nik.unique' => 'NIK sudah terdaftar dalam sistem',
            'nik.regex' => 'NIK harus terdiri dari 16 angka',
            'nik.digits' => 'NIK harus terdiri dari 16 angka',
            'nama_ktp.required' => 'Nama harus diisi',
            'status.in' => 'Status tidak valid',
        ]);

        // Auto-fill nik_ktp dan nama_lengkap with nik and nama_ktp respectively
        $validated['nik_ktp'] = $validated['nik'];
        $validated['nama_lengkap'] = $validated['nama_ktp'];

        $oldValues = $employee->getAttributes();
        $employee->update($validated);
        
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
        $user = auth()->user();
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
        $headers = ['NO', 'NIK KTP', 'NAMA KTP', 'KODE VENDOR', 'POSISI', 'PENEMPATAN', 'TYPE LOKASI', 'AREA KERJA', 'NO REKENING', 'NAMA BANK', 'NAMA DI REKENING', 'STATUS', 'NOTE1'];
        $sampleData = [
            ['1', '1234567890123456', 'John Doe KTP', 'VENDOR001', 'Senior Developer', 'Jakarta', 'Kantor', 'Jawa', '1234567890', 'BCA', 'John Doe', 'Aktif', 'Catatan contoh'],
            ['2', '9876543210123456', 'Jane Smith KTP', 'VENDOR002', 'HR Manager', 'Surabaya', 'Kantor', 'Jawa', '0987654321', 'Mandiri', 'Jane Smith', 'Resign', 'Resign akhir bulan'],
        ];

        $this->renderSpreadsheet($headers, $sampleData);
    }

    private function renderSpreadsheet(array $headers, array $rows): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        // Header row
        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $cell = $column . '1';
            $sheet->setCellValueExplicit($cell, (string) $header, DataType::TYPE_STRING);
        }

        // Data rows (force string to keep NIK precision and leading zeros)
        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $column = Coordinate::stringFromColumnIndex($colIndex + 1);
                $cell = $column . ($rowIndex + 2);
                $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
            }
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $lastRow = count($rows) + 1;

        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFF6B35']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD3D3D3']],
            ],
        ]);

        $widths = [8, 16, 20, 16, 16, 14, 18, 16, 14, 14, 14, 20, 18];
        foreach ($widths as $index => $width) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->getColumnDimension($column)->setWidth($width);
        }

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
                    'error' => 'Format file tidak valid. Pastikan ada kolom NIK KTP atau sesuai template.'
                ], 400);
            }

            $preview = [
                'created' => [],
                'updated' => [],
                'errors' => [],
                'total' => 0
            ];
            
            $row_num = 1;

            foreach (array_slice($rows, 1) as $row) {
                $row_num++;
                
                // Skip empty rows
                if (empty($row[0])) continue;

                try {
                    // Extract data sesuai column mapping
                    $nik_ktp = trim($row[$columnMap['nik_ktp'] ?? 0] ?? '');
                    $nik = $nik_ktp;

                    if (!preg_match('/^[0-9]{16}$/', $nik)) {
                        // Get detail untuk error message
                        $nama_ktp = trim($row[$columnMap['nama_ktp'] ?? 0] ?? '') ?: '-';
                        $posisi = trim($row[$columnMap['posisi'] ?? 0] ?? '') ?: '-';
                        $penempatan = trim($row[$columnMap['penempatan'] ?? 0] ?? '') ?: '-';
                        $preview['errors'][] = "Baris $row_num: NIK KTP harus 16 angka (NIK: $nik, Nama: $nama_ktp, Posisi: $posisi, Penempatan: $penempatan)";
                        continue;
                    }

                    $rawData = [
                        'nik' => $nik,
                        'nik_ktp' => $nik_ktp,
                        'nama_ktp' => $columnMap['nama_ktp'] !== null ? trim($row[$columnMap['nama_ktp']] ?? '') : '',
                        'nama_lengkap' => $columnMap['nama_ktp'] !== null ? trim($row[$columnMap['nama_ktp']] ?? '') : '',
                        'kode_vendor' => $columnMap['kode_vendor'] !== null ? trim($row[$columnMap['kode_vendor']] ?? '') : '',
                        'posisi' => $columnMap['posisi'] !== null ? trim($row[$columnMap['posisi']] ?? '') : '',
                        'penempatan' => $columnMap['penempatan'] !== null ? trim($row[$columnMap['penempatan']] ?? '') : '',
                        'type_lokasi' => $columnMap['type_lokasi'] !== null ? trim($row[$columnMap['type_lokasi']] ?? '') : '',
                        'area_kerja' => $columnMap['area_kerja'] !== null ? trim($row[$columnMap['area_kerja']] ?? '') : '',
                        'no_rekening' => $columnMap['no_rekening'] !== null ? trim($row[$columnMap['no_rekening']] ?? '') : '',
                        'nama_bank' => $columnMap['nama_bank'] !== null ? trim($row[$columnMap['nama_bank']] ?? '') : '',
                        'nama_di_rekening' => $columnMap['nama_di_rekening'] !== null ? trim($row[$columnMap['nama_di_rekening']] ?? '') : '',
                        'status' => $columnMap['status'] !== null ? (trim($row[$columnMap['status']] ?? '') ?: 'Aktif') : 'Aktif',
                        'note1' => $columnMap['note1'] !== null ? trim($row[$columnMap['note1']] ?? '') : '',
                    ];

                    // Check if employee exists
                    $existing = Employee::find($nik);
                    if ($existing) {
                        // Calculate changes
                        $changes = [];
                        foreach (array_keys($rawData) as $field) {
                            $newVal = $rawData[$field];
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
                                $preview['updated'][] = [
                                    'nik' => $existing->nik,
                                    'nama' => $existing->nama_lengkap,
                                    'changes' => $changeDetails
                                ];
                            }
                        }
                    } else {
                        // Create new employee
                        $preview['created'][] = [
                            'nik' => $nik,
                            'nama' => $rawData['nama_lengkap'],
                            'kode_vendor' => $rawData['kode_vendor'],
                            'posisi' => $rawData['posisi'],
                            'type_lokasi' => $rawData['type_lokasi'],
                            'penempatan' => $rawData['penempatan'],
                            'area_kerja' => $rawData['area_kerja'],
                            'nama_bank' => $rawData['nama_bank'],
                            'no_rekening' => $rawData['no_rekening'],
                            'nama_di_rekening' => $rawData['nama_di_rekening'],
                            'status' => $rawData['status'],
                            'note1' => $rawData['note1']
                        ];
                    }
                    
                    $preview['total']++;
                } catch (\Exception $e) {
                    // Add row detail untuk context
                    $nama_ktp = trim($row[$columnMap['nama_ktp'] ?? 0] ?? '') ?: '-';
                    $posisi = trim($row[$columnMap['posisi'] ?? 0] ?? '') ?: '-';
                    $preview['errors'][] = "Baris $row_num (Nama: $nama_ktp, Posisi: $posisi): " . $e->getMessage();
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
                    'message' => 'Format file tidak valid. Pastikan ada kolom NIK KTP atau sesuai template.'
                ], 400);
            }

            $imported = 0;
            $created = [];
            $updated = [];
            $errors = [];
            $row_num = 1;

            foreach (array_slice($rows, 1) as $row) {
                $row_num++;
                
                // Skip empty rows
                if (empty($row[0])) continue;

                try {
                    // Extract data sesuai column mapping
                    $nik_ktp = trim($row[$columnMap['nik_ktp'] ?? 0] ?? '');
                    
                    // Generate NIK dari NIK KTP jika tidak ada NIK terpisah
                    // NIK akan digunakan sebagai identifier unik di database
                    $nik = $nik_ktp; // Use NIK KTP as primary key

                    if (!preg_match('/^[0-9]{16}$/', $nik)) {
                        // Get detail untuk error message
                        $nama_ktp = trim($row[$columnMap['nama_ktp'] ?? 0] ?? '') ?: '-';
                        $posisi = trim($row[$columnMap['posisi'] ?? 0] ?? '') ?: '-';
                        $penempatan = trim($row[$columnMap['penempatan'] ?? 0] ?? '') ?: '-';
                        $errors[] = "Baris $row_num: NIK KTP harus 16 angka (NIK: $nik, Nama: $nama_ktp, Posisi: $posisi, Penempatan: $penempatan)";
                        continue;
                    }

                    $rawData = [
                        'nik' => $nik,
                        'nik_ktp' => $nik_ktp,
                        'nama_ktp' => $columnMap['nama_ktp'] !== null ? trim($row[$columnMap['nama_ktp']] ?? '') : '',
                        'nama_lengkap' => $columnMap['nama_ktp'] !== null ? trim($row[$columnMap['nama_ktp']] ?? '') : '',
                        'kode_vendor' => $columnMap['kode_vendor'] !== null ? trim($row[$columnMap['kode_vendor']] ?? '') : '',
                        'posisi' => $columnMap['posisi'] !== null ? trim($row[$columnMap['posisi']] ?? '') : '',
                        'penempatan' => $columnMap['penempatan'] !== null ? trim($row[$columnMap['penempatan']] ?? '') : '',
                        'type_lokasi' => $columnMap['type_lokasi'] !== null ? trim($row[$columnMap['type_lokasi']] ?? '') : '',
                        'area_kerja' => $columnMap['area_kerja'] !== null ? trim($row[$columnMap['area_kerja']] ?? '') : '',
                        'no_rekening' => $columnMap['no_rekening'] !== null ? trim($row[$columnMap['no_rekening']] ?? '') : '',
                        'nama_bank' => $columnMap['nama_bank'] !== null ? trim($row[$columnMap['nama_bank']] ?? '') : '',
                        'nama_di_rekening' => $columnMap['nama_di_rekening'] !== null ? trim($row[$columnMap['nama_di_rekening']] ?? '') : '',
                        'status' => $columnMap['status'] !== null ? (trim($row[$columnMap['status']] ?? '') ?: 'Aktif') : 'Aktif',
                        'note1' => $columnMap['note1'] !== null ? trim($row[$columnMap['note1']] ?? '') : '',
                    ];

                    // Remove empty strings, keep only non-empty values
                    $dataToUpdate = [];
                    foreach ($rawData as $key => $val) {
                        if ($val !== '' || in_array($key, ['nik', 'nik_ktp'])) {
                            $dataToUpdate[$key] = $val ?: null;
                        }
                    }

                    // Check if employee exists by NIK
                    $existing = Employee::find($nik);
                    if ($existing) {
                        // Calculate changes
                        $changes = [];
                        foreach (array_keys($rawData) as $field) {
                            $newVal = $rawData[$field];
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
                        
                        $created[] = [
                            'nik' => $newEmployee->nik,
                            'nama' => $newEmployee->nama_lengkap,
                            'kode_vendor' => $newEmployee->kode_vendor,
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

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil import $imported data karyawan",
                    'created_count' => count($created),
                    'updated_count' => count($updated),
                    'error_count' => count($errors),
                    'redirect' => route('employee.index')
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal import file: Tidak ada data yang berhasil diproses'
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_merge(...array_values($e->errors())))
            ], 422);
        } catch (\Exception $e) {
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
        $mapping = [
            'nik_ktp' => null,
            'nama_ktp' => null,
            'kode_vendor' => null,
            'posisi' => null,
            'penempatan' => null,
            'type_lokasi' => null,
            'area_kerja' => null,
            'no_rekening' => null,
            'nama_bank' => null,
            'nama_di_rekening' => null,
            'status' => null,
            'note1' => null,
        ];

        // Aliases dengan PRIORITY ORDER - lebih spesifik dulu, generik belakangan
        $aliases = [
            'nik_ktp' => ['NIK KTP', 'NO KTP', 'NIK'],
            'nama_ktp' => ['NAMA KTP', 'NAMA'],  // Hanya NAMA KTP atau NAMA
            'kode_vendor' => ['KODE VENDOR', 'VENDOR'],
            'posisi' => ['POSISI', 'JABATAN'],
            'penempatan' => ['PENEMPATAN', 'LOKASI KERJA', 'LOKASI'],
            'type_lokasi' => ['TYPE LOKASI', 'TIPE LOKASI'],
            'area_kerja' => ['AREA KERJA', 'AREA'],
            'no_rekening' => ['NO REKENING', 'NO. REKENING', 'NOREK'],
            'nama_bank' => ['NAMA BANK'],
            'nama_di_rekening' => ['NAMA REKENING', 'NAMA DI REKENING', 'PEMILIK REKENING'],
            'status' => ['STATUS KERJA', 'STATUS'],
            'note1' => ['NOTE', 'NOTE1', 'CATATAN'],
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

        // Require at least NIK KTP
        if ($mapping['nik_ktp'] === null) {
            return null;
        }

        return $mapping;
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
            
            // Read ALL columns (not just 7)
            for ($i = 0; $i < $cells->length; $i++) {
                $value = '';
                $cell = $cells->item($i);
                if ($cell instanceof \DOMElement) {
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
                    }
                }
                $row[] = $value;
            }
            
            // Include ALL rows (header + data) if not empty
            if (!empty($row[0]) || count($rows) === 0) {
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
        
        // Filter berdasarkan status jika ada
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Apply filters yang sama seperti di index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nik', 'like', "%$search%")
                  ->orWhere('nama_lengkap', 'like', "%$search%")
                  ->orWhere('posisi', 'like', "%$search%");
        }
        
        if ($request->filled('penempatan')) {
            $query->where('penempatan', 'like', "%{$request->penempatan}%");
        }
        
        if ($request->filled('posisi')) {
            $query->where('posisi', 'like', "%{$request->posisi}%");
        }
        
        $employees = $query->orderBy('nik')->get();
        $headers = ['NO', 'NIK KTP', 'NAMA KTP', 'KODE VENDOR', 'JABATAN', 'TYPE LOKASI', 'LOKASI KERJA', 'AREA KERJA', 'STATUS KERJA', 'BANK', 'NOREK', 'NAMA REKENING', 'NOTE1'];
        
        $data = $employees->map(function ($emp, $index) {
            return [
                $index + 1,
                $emp->nik_ktp ?? '-',
                $emp->nama_ktp ?? '-',
                $emp->kode_vendor ?? '-',
                $emp->posisi ?? '-',
                $emp->type_lokasi ?? '-',
                $emp->penempatan ?? '-',
                $emp->area_kerja ?? '-',
                $emp->status ?? 'Aktif',
                $emp->nama_bank ?? '-',
                $emp->no_rekening ?? '-',
                $emp->nama_di_rekening ?? '-',
                $emp->note1 ?? '-',
            ];
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
        $totalAktif = Employee::where('status', 'Aktif')->count();
        $totalResign = Employee::where('status', 'Resign')->count();
        $totalKaryawan = Employee::count();

        // Get detailed data
        $statusFilter = $request->get('status', 'Aktif');
        $query = Employee::where('status', $statusFilter);

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
        $byLocation = Employee::where('status', $statusFilter)
                               ->groupBy('penempatan')
                               ->selectRaw('penempatan, COUNT(*) as count')
                               ->get();

        $byPosition = Employee::where('status', $statusFilter)
                              ->groupBy('posisi')
                              ->selectRaw('posisi, COUNT(*) as count')
                              ->get();

        return view('employee.report', compact(
            'employees',
            'totalAktif',
            'totalResign',
            'totalKaryawan',
            'statusFilter',
            'byLocation',
            'byPosition',
            'penempatan',
            'posisi'
        ));
    }
}
