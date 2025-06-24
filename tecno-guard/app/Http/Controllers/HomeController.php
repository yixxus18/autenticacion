<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $redirectUrl = $request->input('redirect_uri') ?? session('redirect_uri');
        if ($redirectUrl) {
            session()->forget('redirect_uri');
            return view('home', ['redirect_url' => $redirectUrl]);
        }
        return view('home');
    }
}
