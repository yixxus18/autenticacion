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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                "password" => "required|min:8|string|confirmed",
                "phone" => "required|string|max:15",
                "ine_front" => "required|image|mimes:jpeg,png,jpg|max:5120", // Max 5MB
                "direccion" => "required|string|max:255"
            ],
            [
                'ine_front.mimes' => 'La credencial debe ser una imagen en formato JPEG, PNG o JPG.',
                'ine_front.max' => 'La imagen de la credencial no debe superar los 5MB.',
                'direccion.required' => 'Por favor ingresa tu dirección tal como aparece en tu INE.'
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
                    ->withInput($request->except(['password', 'password_confirmation', 'ine_front']));
            }
        }

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
                    ->withInput($request->except(['password', 'password_confirmation', 'ine_front']));
            }
        }

        // Validación local de la imagen INE
        $image = $request->file('ine_front');
        
        // Convertir imagen a base64 para validaciones
        $mimeType = $image->getMimeType();
        $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($image->getRealPath()));
        
        // Validaciones de la imagen
        $imageInfo = getimagesize($image->getRealPath());
        if (!$imageInfo) {
            $errorMessage = 'El archivo subido no es una imagen válida.';
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
                    ->withInput($request->except(['password', 'password_confirmation', 'ine_front']));
            }
        }
        
        // Validar dimensiones mínimas (una INE típica tiene proporciones específicas)
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        if ($width < 300 || $height < 200) {
            $errorMessage = 'La imagen es demasiado pequeña. Por favor sube una imagen clara de tu INE.';
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
                    ->withInput($request->except(['password', 'password_confirmation', 'ine_front']));
            }
        }
        
        // Validar proporción de aspecto (las INEs tienen una proporción aproximada de 1.6:1)
        $aspectRatio = $width / $height;
        if ($aspectRatio < 1.4 || $aspectRatio > 1.8) {
            Log::warning('Imagen con proporción inusual para INE', [
                'width' => $width,
                'height' => $height,
                'aspect_ratio' => $aspectRatio
            ]);
        }
        
        // Log de la validación exitosa
        Log::info('Imagen INE validada localmente', [
            'user_email' => $request->email,
            'image_dimensions' => $width . 'x' . $height,
            'image_size' => $image->getSize(),
            'mime_type' => $mimeType
        ]);
        
        // Usar la dirección proporcionada por el usuario
        $address = trim($request->direccion);

        // Actualizar el usuario existente en lugar de crear uno nuevo
        $existingUser->name = $request->name;
        $existingUser->email = $request->email;
        $existingUser->password = Hash::make($request->password);
        $existingUser->direccion = $address;
        $existingUser->save();

        // Guardar la imagen de la INE en el bucket
        if ($request->hasFile('ine_front')) {
            try {
                $image = $request->file('ine_front');
                $timestamp = now()->timestamp;
                $extension = $image->getClientOriginalExtension();
                $fileName = "ine_{$existingUser->id}_{$timestamp}.{$extension}";
                $path = "user_ines/{$fileName}";

                // Subir el archivo al bucket configurado
                Storage::disk(env('FILESYSTEM_DISK', 's3'))->put($path, file_get_contents($image), 'public');

                // La imagen se sube, pero la ruta no se guarda en la base de datos por ahora.
                // Si en el futuro se añade una columna 'ine_front_path' a la tabla 'users',
                // se pueden descomentar las siguientes líneas:
                // $existingUser->ine_front_path = $path;
                // $existingUser->save();

                Log::info('Imagen de INE subida exitosamente al bucket.', ['user_id' => $existingUser->id, 'path' => $path]);

            } catch (\Exception $e) {
                Log::error('Error al subir la imagen de la INE al bucket.', [
                    'user_id' => $existingUser->id,
                    'error' => $e->getMessage()
                ]);
                // No detenemos el registro si la subida falla, pero lo registramos.
            }
        }

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
}
