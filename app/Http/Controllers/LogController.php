<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Hanya Super Admin yang bisa akses
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'Super Admin') {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }
    
    /**
     * Display all activity logs
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();
        
        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }
        
        // Search by description
        if ($request->filled('search')) {
            $query->where('description', 'like', "%{$request->search}%")
                  ->orWhere('model_id', 'like', "%{$request->search}%");
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Support per_page parameter (default 10)
        $perPage = $request->get('per_page', 10);
        $logs = $query->paginate($perPage)->appends($request->query());
        
        // Get unique values untuk filter dropdowns
        $actions = ActivityLog::distinct()->pluck('action')->sort();
        $users = ActivityLog::select('user_id')->distinct()->with('user')->get();
        $modelTypes = ActivityLog::distinct()->pluck('model_type')->filter()->sort();
        
        return view('log.index', compact('logs', 'actions', 'users', 'modelTypes'));
    }
}
