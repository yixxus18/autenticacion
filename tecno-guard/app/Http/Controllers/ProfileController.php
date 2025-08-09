<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\VerificamexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;


class ProfileController extends Controller
{
    protected $verificamexService;

    public function __construct(VerificamexService $verificamexService)
    {
        $this->verificamexService = $verificamexService;
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('role');

        return response()->json([
            'message' => 'Información del usuario obtenida correctamente',
            'data' => $user,
            'status' => true
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'current_password' => 'required_with:direccion|string',
            'ine_front' => 'required_with:direccion|image',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();
        
        $updateData = array_filter($validated, function ($key) {
            return !in_array($key, ['current_password', 'ine_front']);
        }, ARRAY_FILTER_USE_KEY);

        if ($request->has('direccion') && !empty($request->direccion)) {
            if (!in_array($user->role_id, [1, 4])) {
                throw new AuthorizationException('No tienes permisos para editar la dirección.');
            }

            if (!Hash::check($request->current_password, $user->password)) {
                 return response()->json([
                    'error' => 'invalid_credentials',
                    'message' => 'La contraseña actual no es correcta.',
                    'data' => null,
                    'status' => false
                ], 401);
            }

            $image = $request->file('ine_front');
            $mimeType = $image->getMimeType();
            $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($image->getRealPath()));

            $headers = $this->verificamexService->getAuthHeaders();
            $baseUrl = $this->verificamexService->getBaseUrl();

            $response = Http::withoutVerifying()
                ->withHeaders($headers)
                ->timeout(30)
                ->post($baseUrl . '/identity/v1/ocr/obverse', [
                    'ine_front' => $base64Image,
                ]);

            if (!$response->successful()) {
                Log::error('API Error Response on profile update: ' . $response->body());
                return response()->json([
                    'error' => 'ine_validation_failed',
                    'message' => 'Error al procesar la imagen de la INE. Intente de nuevo.',
                    'data' => null,
                    'status' => false
                ], 422);
            }
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'data' => ['user' => $user->fresh()],
            'status' => true
        ], 200);
    }
}
