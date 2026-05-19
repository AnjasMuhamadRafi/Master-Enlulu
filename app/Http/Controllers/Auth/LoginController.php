<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Jika remember me di-check, simpan email + password ke session
        if ($request->filled('remember')) {
            $request->session()->put('remembered_email', $credentials['email']);
            $request->session()->put('remembered_password', $credentials['password']);
        } else {
            // Jika tidak di-check, hapus remembered data
            $request->session()->forget(['remembered_email', 'remembered_password']);
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Simpan remembered data sebelum flush session
        $remembered_email = $request->session()->get('remembered_email');
        $remembered_password = $request->session()->get('remembered_password');
        
        $request->session()->flush();
        
        // Restore remembered data setelah flush (jika ada)
        if ($remembered_email) {
            session(['remembered_email' => $remembered_email]);
        }
        if ($remembered_password) {
            session(['remembered_password' => $remembered_password]);
        }

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

