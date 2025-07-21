<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('role');

        return response()->json([
            'message' => 'Información del usuario obtenida correctamente',
            'data' => ['user' => $user],
            'status' => true
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255'
        ]);

        $user->update(array_filter($validated));

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'data' => ['user' => $user],
            'status' => true
        ], 200);
    }
}
