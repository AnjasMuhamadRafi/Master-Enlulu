<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Employee;
use App\Services\ContractDocumentService;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function __construct(private ContractDocumentService $documents)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $contracts = $this->accessibleContracts()
            ->with('employee')
            ->latest()
            ->paginate(15);

        return view('contract.index', compact('contracts'));
    }

    public function create()
    {
        $employees = $this->accessibleEmployees()
            ->orderBy('nama_lengkap')
            ->get(['nik', 'nik_ktp', 'nama_lengkap', 'nik_enlulu', 'no_pks_masuk', 'nama_customer', 'tanggal_masuk', 'tanggal_keluar']);

        return view('contract.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_nik' => ['required', 'string', 'exists:employees,nik'],
            'contract_number' => ['nullable', 'string', 'max:100'],
            'contract_date' => ['required', 'date'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $employee = $this->accessibleEmployees()->whereKey($validated['employee_nik'])->firstOrFail();

        $contract = Contract::create([
            'employee_nik' => $employee->nik,
            'contract_number' => $validated['contract_number'] ?: ($employee->no_pks_masuk ?: 'PKS-' . now()->format('YmdHis')),
            'contract_date' => $validated['contract_date'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? $employee->tanggal_keluar,
            'template_name' => ContractDocumentService::TEMPLATE_NAME,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('contract.index')
            ->with('success', "Kontrak {$contract->contract_number} berhasil dibuat.")
            ->with('created_contract_id', $contract->id);
    }

    public function edit(Contract $contract)
    {
        abort_unless($this->canAccess($contract), 403);

        return view('contract.edit', compact('contract'));
    }

    public function update(Request $request, Contract $contract)
    {
        abort_unless($this->canAccess($contract), 403);

        $validated = $request->validate([
            'contract_number' => ['required', 'string', 'max:100'],
            'contract_date' => ['required', 'date'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $contract->update($validated);

        return redirect()->route('contract.index')->with('success', 'Kontrak berhasil diperbarui.');
    }

    public function download(Contract $contract)
    {
        abort_unless($this->canAccess($contract), 403);
        $contract->load('employee');
        $path = $this->documents->createDocument($contract);

        return response()->download($path, $this->documents->downloadName($contract))
            ->deleteFileAfterSend(true);
    }

    public function destroy(Contract $contract)
    {
        abort_unless($this->canAccess($contract), 403);
        $contract->delete();

        return redirect()->route('contract.index')->with('success', 'Kontrak berhasil dihapus.');
    }

    private function accessibleEmployees()
    {
        $query = Employee::query();
        $user = auth()->user();

        if ($user && $user->isAdminPic()) {
            $query->whereIn('posisi', $user->getManagedPositions());
        }

        return $query;
    }

    private function accessibleContracts()
    {
        $query = Contract::query();
        $user = auth()->user();

        if ($user && $user->isAdminPic()) {
            $query->whereHas('employee', fn ($employees) => $employees->whereIn('posisi', $user->getManagedPositions()));
        }

        return $query;
    }

    private function canAccess(Contract $contract): bool
    {
        return $this->accessibleContracts()->whereKey($contract->id)->exists();
    }
}
