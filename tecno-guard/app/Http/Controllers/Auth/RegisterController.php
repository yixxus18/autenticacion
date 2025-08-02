<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\VerificamexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\ValidatorEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class RegisterController extends Controller
{
    protected $verificamexService;

    public function __construct(VerificamexService $verificamexService)
    {
        $this->middleware('guest');
        $this->verificamexService = $verificamexService;
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
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'validation_failed',
                    'message' => 'Algunos de los datos proporcionados no son válidos. Por favor, revisa los errores y corrige los campos indicados.',
                    'data' => [ 'errors' => $validate->errors() ],
                    'status' => false
                ], 422);
            } else {
                return back()
                    ->withErrors($validate)
                    ->withInput($request->except(['password', 'ine_front']));
            }
        }

        // Verificar si existe un usuario con el número de teléfono que no haya confirmado su email
        $existingUser = User::where('phone', $request->phone)
            ->whereNull('email_verified_at')
            ->first();

        if (!$existingUser) {
            $errorMessage = 'No tienes autorización para registrarte. Contacta al administrador para que registre tu número de teléfono.';

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'unauthorized',
                    'message' => $errorMessage,
                    'status' => false
                ], 403);
            } else {
                return back()
                    ->withErrors(['phone' => $errorMessage])
                    ->withInput($request->except(['password', 'ine_front']));
            }
        }

        $image = $request->file('ine_front');
        $mimeType = $image->getMimeType();
        $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($image->getRealPath()));

        $headers = $this->verificamexService->getAuthHeaders();
        $baseUrl = $this->verificamexService->getBaseUrl();

        $response = Http::withoutVerifying()
            ->withHeaders($headers)
            ->timeout(30) // Opcional: puedes obtenerlo del servicio si lo necesitas
            ->post($baseUrl . '/identity/v1/ocr/obverse', [
                'ine_front' => $base64Image,
            ]);

        // Log detallado de la respuesta
        Log::info('Response status: ' . $response->status());
        Log::info('Response headers: ' . json_encode($response->headers()));
        Log::info('Response body preview: ' . substr($response->body(), 0, 500));

        $address = '';
        if ($response->successful()) {
            $apiResponse = $response->json();
            Log::info('API Response: ' . json_encode($apiResponse));
            $parseOcr = $apiResponse['data']['parse_ocr'] ?? [];
            $addressData = collect($parseOcr)->firstWhere('type', 'PermanentAddress');
            Log::info('Address data: ' . json_encode($addressData));
            if ($addressData && isset($addressData['value'])) {
                $address = $addressData['value'];
            } else {
                if ($request->wantsJson()) {
                    return response()->json([
                        'error' => 'validation_failed',
                        'message' => 'No se pudo encontrar la dirección en la imagen.',
                        'data' => [ 'errors' => ['ine_front' => ['No se pudo encontrar la dirección en la imagen.']] ],
                        'status' => false
                    ], 422);
                } else {
                    return back()
                        ->withErrors(['ine_front' => 'No se pudo encontrar la dirección en la imagen.'])
                        ->withInput($request->except(['password', 'ine_front']));
                }
            }
        } else {
            $errorMessage = 'Error al procesar la imagen de la INE. Intente de nuevo.';
            $apiError = $response->json();
            Log::error('API Error Response: ' . json_encode($apiError));
            Log::error('Full response body: ' . $response->body());

            if ($apiError && isset($apiError['message'])) {
                $errorMessage .= ' Detalle: ' . $apiError['message'];
            } else {
                $errorMessage .= ' Status: ' . $response->status() . ' - ' . $response->reasonPhrase();
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'validation_failed',
                    'message' => $errorMessage,
                    'data' => [ 'errors' => ['ine_front' => [$errorMessage]] ],
                    'status' => false
                ], 422);
            } else {
                return back()
                    ->withErrors(['ine_front' => $errorMessage])
                    ->withInput($request->except(['password', 'ine_front']));
            }
        }

        // Actualizar el usuario existente en lugar de crear uno nuevo
        $existingUser->name = $request->name;
        $existingUser->email = $request->email;
        $existingUser->password = Hash::make($request->password);
        $existingUser->direccion = $address;
        $existingUser->save();

        $signedroute = URL::temporarySignedRoute(
            'activate',
            now()->addMinutes(10),
            ['user' => $existingUser->id]
        );

        Mail::to($request->email)->send(new ValidatorEmail($signedroute));

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Usuario registrado exitosamente. Revisa tu correo para activar tu cuenta.',
                'data' => [ 'user_id' => $existingUser->id ],
                'status' => 201
            ], 201);
        } else {
            return redirect()->route('login')
                ->with('success', 'Se ha enviado un mensaje a tu correo para activar tu cuenta.');
        }
    }

    public function activate(User $user)
    {
        $user->is_active = true;
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('login')
            ->with('success', '¡Cuenta activada con éxito! Ahora puedes iniciar sesión.');
    }

    // Método de prueba para verificar la API
    public function testApi()
    {
        try {
            $headers = $this->verificamexService->getAuthHeaders();
            $baseUrl = $this->verificamexService->getBaseUrl();

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->get($baseUrl . '/health');

            return response()->json([
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json(), // Usar json() para una mejor visualización
                'successful' => $response->successful(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception caught',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
