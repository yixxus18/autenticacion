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
use Illuminate\Support\Facades\Http;

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
            $request->all(),
            [
                "name" => "required|max:30",
                "email" => "required|unique:users|email",
                "password" => "required|min:8|string",
                "phone" => "required|string|max:15",
                "ine_front" => "required|image"
            ]
        );

        if ($validate->fails()) {
            return back()
                ->withErrors($validate)
                ->withInput($request->except(['password', 'ine_front']));
        }

        $image = $request->file('ine_front');
        $mimeType = $image->getMimeType();
        $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($image->getRealPath()));

        $apiKey = env('VERIFICAMEX_API_KEY');

        $response = Http::withToken($apiKey)->post('https://api.verificamex.com/identity/v1/ocr/obverse', [
            'ine_front' => $base64Image,
        ]);

        $address = '';
        if ($response->successful()) {
            $apiResponse = $response->json();
            $parseOcr = $apiResponse['data']['parse_ocr'] ?? [];
            $addressData = collect($parseOcr)->firstWhere('type', 'PermanentAddress');

            if ($addressData && isset($addressData['value'])) {
                $address = $addressData['value'];
            } else {
                return back()
                    ->withErrors(['ine_front' => 'No se pudo encontrar la dirección en la imagen.'])
                    ->withInput($request->except(['password', 'ine_front']));
            }
        } else {
            $errorMessage = 'Error al procesar la imagen de la INE. Intente de nuevo.';
            $apiError = $response->json();
            if ($apiError && isset($apiError['message'])) {
                $errorMessage .= ' Detalle: ' . $apiError['message'];
            }
            return back()
                ->withErrors(['ine_front' => $errorMessage])
                ->withInput($request->except(['password', 'ine_front']));
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role_id = 3;
        $user->is_active = false;
        $user->password = Hash::make($request->password);
        $user->phone = $request->phone;
        $user->direccion = $address;
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
