<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return redirect('/home');
    }

    public function redirect(Request $request)
    {
        $redirectData = $request->session()->get('Redirect');
        if (isset($redirectData)) {
            return view('includes.redirect');
        } else {
            abort('404');
        }
    }

    public function dashboard()
    {
        return view('dashboard');
    }
}
