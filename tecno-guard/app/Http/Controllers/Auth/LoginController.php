<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'verifyTwoFactorForm', 'verifyTwoFactor']);
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            if (!$user->email_verified_at || !$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta no está activada. Por favor, verifica tu correo electrónico.',
                ])->onlyInput('email');
            }

            $this->generateTwoFactorCode($user);
            Mail::to($user->email)->send(new TwoFactorCodeMail($user, $user->two_factor_code));
            $request->session()->put('2fa_user_id', $user->id);
            Auth::logout();
            return redirect()->route('2fa.form');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'error' => 'invalid_credentials',
                'message' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
                'data' => null,
                'status' => false
            ], 401);
        } else {
            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }
    }

    public function showTwoFactorForm()
    {
        if (!session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.two-factor-challenge');
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'two_factor_code' => 'required|numeric',
        ]);
        $user = User::find(session('2fa_user_id'));
        if (!$user) {
            return redirect()->route('login')->withErrors(['two_factor_code' => 'Usuario no encontrado para 2FA.']);
        }
        if ($user->two_factor_code && $user->two_factor_expires_at > now() && $request->two_factor_code == $user->two_factor_code) {
            $this->resetTwoFactorCode($user);
            Auth::login($user, $request->filled('remember'));
            $request->session()->forget('2fa_user_id');
            $request->session()->regenerate();
            return redirect()->intended('home');
        }
        return back()->withErrors([
            'two_factor_code' => 'El código de dos factores es inválido o ha expirado.',
        ]);
    }

    protected function generateTwoFactorCode(User $user)
    {
        $user->timestamps = false;
        $user->two_factor_code = random_int(100000, 999999);
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();
    }

    protected function resetTwoFactorCode(User $user)
    {
        $user->timestamps = false;
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $code = random_int(100000, 999999);
        $user = User::where('email', $request->email)->first();
        $user->update(['two_factor_code' => $code, 'two_factor_expires_at' => now()->addMinutes(10)]);

        Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));

        return redirect()->route('password.verify.form')->with('email', $request->email);
    }

    public function showVerifyCodeForm()
    {
        if (!session()->has('email')) {
            return redirect()->route('password.request');
        }
        return view('auth.verify-code');
    }

    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)
                   ->where('two_factor_code', $request->code)
                   ->where('two_factor_expires_at', '>', now())
                   ->first();

        if (!$user) {
            return back()->withErrors(['code' => 'Código inválido o expirado']);
        }

        return redirect()->route('password.reset')->with([
            'email' => $user->email,
            'code' => $request->code
        ]);
    }

    public function showResetPasswordForm()
    {
        if (!session()->has('email') || !session()->has('code')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::where('email', $request->email)
                   ->where('two_factor_code', $request->code)
                   ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Usuario no encontrado']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'two_factor_code' => null,
            'two_factor_expires_at' => null
        ]);

        return redirect()->route('login')->with('status', 'Contraseña actualizada correctamente');
    }

    public function resendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $code = random_int(100000, 999999);
        $user = User::where('email', $request->email)->first();
        $user->update(['two_factor_code' => $code, 'two_factor_expires_at' => now()->addMinutes(10)]);

        Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));

        return response()->json(['success' => true]);
    }
}
