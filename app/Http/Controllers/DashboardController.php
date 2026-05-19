<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        /** @var User|null $user */
        $user = auth()->user();
        $query = Employee::query();
        $adminPicQuery = Employee::query();
        
        // Apply access control filtering if user is Admin/PIC
        if ($user && $user->isAdminPic()) {
            $adminPicDepartments = config('positions.admin_pic_departments', []);
            $managedPositions = $adminPicDepartments[$user->handled_position] ?? [];
            
            if (!empty($managedPositions)) {
                // Filter statistics query
                $query->whereIn('posisi', $managedPositions);
                // Filter admin pic query
                $adminPicQuery->whereIn('posisi', $managedPositions);
            }
        }
        
        // Get statistics (filtered by role if Admin/PIC)
        $employee_count = $query->count();
        $active_employee = (clone $query)->where('status', 'Aktif')->count();
        $training_employee = (clone $query)->where('status', 'Training')->count();
        $inactive_employee = (clone $query)->where('status', 'Resign')->count();
        $user_count = User::count();
        
        // Get ADMIN/PIC employees (filtered by role if user is Admin/PIC)
        $adminPicRoles = config('positions.admin_pic_roles', []);
        $adminPicEmployees = $adminPicQuery->whereIn('posisi', $adminPicRoles)
            ->where('status', 'Aktif')
            ->get();
        
        return view('dashboard', compact(
            'employee_count',
            'active_employee',
            'training_employee',
            'inactive_employee',
            'user_count',
            'adminPicEmployees'
        ));
    }
}
