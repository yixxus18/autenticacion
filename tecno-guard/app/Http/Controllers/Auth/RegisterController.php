<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\ValidatorEmail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validate = Validator::make(
            $request->all(),[
                "name" => "required|max:30",
                "email" => "required|unique:users|email",
                "password" => "required|min:8|string",
                "phone" => "required|string|max:15",
                "direccion" => "required|string|max:255"
            ]
        );

        if($validate->fails())
        {
            return back()
                ->withErrors($validate)
                ->withInput($request->except('password'));
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role_id = 3;
        $user->is_active = false;
        $user->password = Hash::make($request->password);
        $user->phone = $request->phone;
        $user->direccion = $request->direccion;
        $user->save();

        $signedroute = URL::temporarySignedRoute(
            'activate',
            now()->addMinutes(10),
            ['user' => $user->id]
        );

        Mail::to($request->email)->send(new ValidatorEmail($signedroute));

        return redirect()->route('login')
            ->with('success', 'Se ha enviado un mensaje a tu correo para activar tu cuenta.');
    }

    public function activate(User $user)
    {
        $user->is_active = true;
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('login')
            ->with('success', '¡Cuenta activada con éxito! Ahora puedes iniciar sesión.');
    }
}
