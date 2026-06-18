<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Report Karyawan Aktif
     */
    public function aktif(Request $request)
    {
        $query = Employee::where('status', 'Aktif');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nik', 'like', "%$search%")
                  ->orWhere('nama_lengkap', 'like', "%$search%")
                  ->orWhere('posisi', 'like', "%$search%");
            });
        }
        
        // Filter by penempatan
        if ($request->filled('penempatan')) {
            $query->where('penempatan', 'like', "%{$request->penempatan}%");
        }
        
        // Filter by posisi
        if ($request->filled('posisi')) {
            $query->where('posisi', 'like', "%{$request->posisi}%");
        }
        
        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000])) {
            $perPage = 15;
        }
        
        $employees = $query->paginate($perPage)->appends($request->query());
        $penempatan = Employee::where('status', 'Aktif')->distinct()->pluck('penempatan')->filter();
        $posisi = Employee::where('status', 'Aktif')->distinct()->pluck('posisi')->filter();
        
        return view('report.aktif', compact('employees', 'penempatan', 'posisi'));
    }
    
    /**
     * Report Karyawan Resign
     */
    public function resign(Request $request)
    {
        $query = Employee::where('status', 'Resign');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nik', 'like', "%$search%")
                  ->orWhere('nama_lengkap', 'like', "%$search%")
                  ->orWhere('posisi', 'like', "%$search%");
            });
        }

        // Filter by penempatan
        if ($request->filled('penempatan')) {
            $query->where('penempatan', 'like', "%{$request->penempatan}%");
        }

        // Filter by posisi
        if ($request->filled('posisi')) {
            $query->where('posisi', 'like', "%{$request->posisi}%");
        }

        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000])) {
            $perPage = 15;
        }

        $employees = $query->paginate($perPage)->appends($request->query());
        $penempatan = Employee::where('status', 'Resign')->distinct()->pluck('penempatan')->filter();
        $posisi = Employee::where('status', 'Resign')->distinct()->pluck('posisi')->filter();

        return view('report.resign', compact('employees', 'penempatan', 'posisi'));
    }

    /**
     * Report Karyawan Training
     */
    public function training(Request $request)
    {
        $query = Employee::where('status', 'Training');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nik', 'like', "%$search%")
                  ->orWhere('nama_lengkap', 'like', "%$search%")
                  ->orWhere('posisi', 'like', "%$search%");
            });
        }

        if ($request->filled('penempatan')) {
            $query->where('penempatan', 'like', "%{$request->penempatan}%");
        }

        if ($request->filled('posisi')) {
            $query->where('posisi', 'like', "%{$request->posisi}%");
        }

        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000])) {
            $perPage = 15;
        }

        $employees = $query->paginate($perPage)->appends($request->query());
        $penempatan = Employee::where('status', 'Training')->distinct()->pluck('penempatan')->filter();
        $posisi = Employee::where('status', 'Training')->distinct()->pluck('posisi')->filter();

        return view('report.training', compact('employees', 'penempatan', 'posisi'));
    }

    /**
     * Report Karyawan Fraud
     */
    public function fraud(Request $request)
    {
        $query = Employee::where('status', 'Fraud');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nik', 'like', "%$search%")
                  ->orWhere('nama_lengkap', 'like', "%$search%")
                  ->orWhere('posisi', 'like', "%$search%");
            });
        }

        if ($request->filled('penempatan')) {
            $query->where('penempatan', 'like', "%{$request->penempatan}%");
        }

        if ($request->filled('posisi')) {
            $query->where('posisi', 'like', "%{$request->posisi}%");
        }

        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 25, 50, 100, 500, 1000])) {
            $perPage = 15;
        }

        $employees = $query->paginate($perPage)->appends($request->query());
        $penempatan = Employee::where('status', 'Fraud')->distinct()->pluck('penempatan')->filter();
        $posisi = Employee::where('status', 'Fraud')->distinct()->pluck('posisi')->filter();

        return view('report.fraud', compact('employees', 'penempatan', 'posisi'));
    }
}
