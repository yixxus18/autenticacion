# Guía de Implementación: Microservicio de Autenticación con Laravel Passport (Federación)

Esta guía detalla la configuración y el uso de Laravel Passport como un microservicio de autenticación federada, donde la autenticación se realiza en un servicio centralizado y las aplicaciones cliente consumen tokens para acceder a APIs de negocio.

## 1. Introducción al Microservicio de Autenticación

El objetivo es separar la lógica de autenticación en un servicio independiente (`tecno-ward` en tu caso) y permitir que otras aplicaciones (por ejemplo, tu "API de negocio") utilicen este servicio para autenticar usuarios y obtener tokens de acceso. Este patrón se conoce como Autenticación Federada o Single Sign-On (SSO) en ciertos contextos.

Tu microservicio de autenticación (`tecno-ward`) será el **Servidor de Autorización OAuth 2.0**.

## 2. Configuración Inicial de Laravel Passport (Servicio de Autenticación)

Asegúrate de que Laravel esté instalado y funcionando.

### 2.1 Instalación y Migraciones de Passport

Primero, instala Passport a través de Composer y ejecuta sus migraciones para crear las tablas necesarias en tu base de datos:

```bash
composer require laravel/passport
php artisan migrate
```

### 2.2 Generación de Claves de Cifrado

Passport necesita claves para cifrar y descifrar tokens. Genera estas claves:

```bash
php artisan passport:keys
```

### 2.3 Publicar Vistas de Passport

Para personalizar las páginas de autorización de Passport (como la que ya modificamos), publica sus vistas. Esto copiará los archivos Blade a `resources/views/vendor/passport`:

```bash
php artisan vendor:publish --tag=passport-views
```

### 2.4 Configuración de Clientes de Passport

Necesitas registrar clientes en tu base de datos para que las aplicaciones puedan interactuar con Passport. Usaremos `Passport::client` en el `AuthServiceProvider` para definir clientes por defecto.

**`tecno-ward/app/Providers/AuthServiceProvider.php`**

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport; // Asegúrate de que esta línea esté presente

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\\Models\\Model' => 'App\\Policies\\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // No es necesario llamar a Passport::routes() en versiones recientes si ya ejecutaste migrate
        // Passport::routes(); // Línea eliminada previamente

        // Configurar Passport
        Passport::tokensCan([
            'access-api' => 'Acceder a los endpoints de la API'
        ]);

        // Configurar el tiempo de expiración de los tokens (ajusta según tus necesidades)
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Define el cliente de acceso personal (para emitir tokens personales)
        // Puedes definir un cliente específico para el grant de código de autorización aquí si lo deseas,
        // pero Laravel lo maneja automáticamente si no tienes un client_id/secret en config/passport.php
        // Puedes crear clientes en la BD con `php artisan passport:client --personal`
        // o `php artisan passport:client --password` para otros grants.
    }
}
```

### 2.5 Configuración de Autenticación y API en Laravel

Asegúrate de que tu modelo `User` utilice el trait `HasApiTokens`.

**`tecno-ward/app/Models/User.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens; // Asegúrate de que esta línea esté presente
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Asegúrate de que HasApiTokens esté aquí

    // ... (rest of your model)
}
```

**`tecno-ward/config/auth.php`**

Asegúrate de que tu guard `api` use el driver `passport`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'passport', // Asegúrate de que sea 'passport'
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],

    // 'users' => [
    //     'driver' => 'database',
    //     'table' => 'users',
    // ],
],
```

### 2.6 Variables de Entorno (`.env`)

Necesitas asegurarte de que tu `APP_URL` esté correctamente configurada. Los `client_id` y `client_secret` de Passport generalmente se obtienen de la tabla `oauth_clients` después de ejecutar `php artisan passport:install` o `php artisan passport:client`.

```dotenv
APP_URL=http://localhost:8000 # Tu URL de la aplicación de autenticación
# ... otras variables ...

# Estos valores corresponden al "Personal Access Client" (ID 1 por defecto)
# O el cliente que uses para tu grant de código de autorización
# Puedes ver los IDs y secretos en la tabla oauth_clients
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=3 # Reemplaza con el ID de tu cliente Personal Access (o el que uses para este flujo)
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=qexPwMDkIRw1UcJEL8VqCPXcUOj7jzFvmATeQf4... # Reemplaza con el secret
```

## 3. Controladores (Microservicio de Autenticación: `tecno-ward`)

### 3.1 `OAuthController.php`

Este controlador maneja el flujo de autenticación, incluyendo el login tradicional y el flujo de código de autorización de OAuth 2.0.

**`tecno-ward/app/Http/Controllers/Auth/OAuthController.php`**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Passport\Client; // Ya está importado, déjalo
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Puede que no sea estrictamente necesario aquí si no creas usuarios
use Illuminate\Support\Facades\Log; // Importante para depuración
use GuzzleHttp\Client as GuzzleClient; // Para el cliente HTTP de Guzzle

class OAuthController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión de la aplicación.
     * Si el usuario ya está autenticado, lo redirige al home.
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    /**
     * Maneja el intento de login tradicional (email y contraseña).
     * Si es exitoso, redirige a iniciar el flujo OAuth.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            Log::info('Login tradicional exitoso para el usuario: ' . $credentials['email']);

            // Después de un login tradicional exitoso, redirigir a iniciar el flujo OAuth
            return redirect()->route('auth.initiate');
        }

        Log::warning('Intento de login tradicional fallido para el email: ' . $credentials['email']);
        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Inicia el flujo de código de autorización de OAuth 2.0.
     * Redirige al usuario a la página de autorización de Passport.
     */
    public function initiateAuth(Request $request)
    {
        if (Auth::check()) {
            Log::info('Usuario ya autenticado, iniciando flujo OAuth para obtener token.');
        } else {
            // Esto no debería pasar si el login tradicional fue exitoso,
            // pero es un fallback. El usuario debería estar autenticado aquí.
            Log::warning('initiateAuth llamado sin usuario autenticado. Redirigiendo al login.');
            return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
        }

        $query = http_build_query([
            'client_id' => config('passport.personal_access_client.id'), // Usar el ID del cliente configurado
            'redirect_uri' => config('app.url') . '/auth/callback',
            'response_type' => 'code',
            'scope' => 'access-api', // Define tus scopes aquí
            'state' => csrf_token(), // Protección CSRF
        ]);

        return redirect(config('app.url') . '/oauth/authorize?' . $query);
    }

    /**
     * Maneja la respuesta del servidor de autorización de OAuth (el callback).
     * Intercambia el código de autorización por un token de acceso.
     */
    public function callback(Request $request)
    {
        Log::info('OAuth Callback iniciado.');

        if ($request->has('error')) {
            Log::error('Error en la autenticación (callback): ' . $request->error);
            return response()->json(['error' => 'Error en la autenticación'], 400);
        }

        // Cliente Guzzle para hacer la solicitud POST al endpoint /oauth/token
        $http = new GuzzleClient();

        try {
            Log::info('Intentando obtener el token de acceso desde /oauth/token');
            $response = $http->post(config('app.url') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('passport.personal_access_client.id'),
                    'client_secret' => config('passport.personal_access_client.secret'),
                    'redirect_uri' => config('app.url') . '/auth/callback',
                    'code' => $request->code,
                    'scope' => 'access-api', // Los mismos scopes que solicitaste
                ],
            ]);

            $token = json_decode((string) $response->getBody(), true);
            Log::info('Token de acceso obtenido: ' . json_encode($token));

            // Obtener el usuario autenticado en la sesión actual
            // Este usuario ya debería estar autenticado por el login tradicional antes de initiateAuth
            $user = Auth::user();
            Log::info('Usuario autenticado en la sesión: ' . ($user ? $user->id . ' - ' . $user->email : 'Ninguno'));

            if (!$user) {
                Log::error('Usuario no encontrado o no autenticado en la sesión después del callback de OAuth.');
                // Esto es un punto crítico: si no hay usuario autenticado aquí, algo falló en la sesión.
                return redirect()->route('login')->with('error', 'Error: Usuario no autenticado después de la autorización.');
            }

            // Crear un token personal de acceso (Passport lo usa para tokens de API, no OAuth directamente)
            // Aquí se genera un token de acceso PERSONAL de la aplicación cliente,
            // no es el token de acceso OAuth del servidor de autorización.
            // Para consumo desde una API de negocio, usarías el $token['access_token']
            // directamente, pero Laravel Passport usa este personal token para proteger rutas web/aplicaciones.
            $personalAccessToken = $user->createToken('Personal Access Token')->accessToken;
            Log::info('Token personal creado para el usuario: ' . $user->id);

            // Redirigir a la página principal con el token para que la aplicación cliente lo capture.
            // Es crucial pasar el token de acceso obtenido del servidor de autorización aquí,
            // no el personalAccessToken (a menos que quieras usarlo para la sesión web).
            // Para una API de negocio, la aplicación cliente capturará este token de la URL.
            return redirect()->route('home')->with(['token' => $token['access_token']]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody(true);
            Log::error('Error en OAuth callback (Guzzle Client): ' . $e->getMessage() . ' - Response: ' . $responseBody);
            return response()->json(['error' => 'Error al obtener el token: ' . $responseBody], 400);
        } catch (\Exception $e) {
            Log::error('Error inesperado en OAuth callback: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener el token: ' . $e->getMessage()], 400);
        }
    }
}
```

## 4. Rutas (Microservicio de Autenticación: `tecno-ward`)

### 4.1 `tecno-ward/routes/web.php`

Define las rutas para el flujo de autenticación web.

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\OAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta de bienvenida (opcional)
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Rutas de Login tradicional
Route::get('/login', [OAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [OAuthController::class, 'login'])->name('login.post');

// Rutas del flujo OAuth
Route::get('/auth/initiate', [OAuthController::class, 'initiateAuth'])->name('auth.initiate');
Route::get('/auth/callback', [OAuthController::class, 'callback'])->name('auth.callback');

// Ruta de Home/Dashboard (requiere autenticación de sesión web)
Route::get('/home', function () {
    // Si estás pasando el token en la URL, puedes capturarlo aquí para mostrarlo o guardarlo
    // En una aplicación real, probablemente harías una redirección en el frontend después de capturar el token
    return view('home');
})->name('home');

// Ruta para cerrar sesión (opcional)
Route::get('/logout', function (Request $request) {
    Auth::guard('web')->logout(); // Cierra la sesión web
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login')->with('success', 'Has cerrado sesión.');
})->name('logout');
```

## 5. Vistas (Microservicio de Autenticación: `tecno-ward`)

### 5.1 `tecno-ward/resources/views/auth/login.blade.php`

Este formulario permite el login tradicional y luego inicia el flujo OAuth.

```html
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión - TecnoWeb</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- CDN de Toastr y jQuery -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <!-- Tailwind CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <!-- ... tus estilos CSS personalizados ... -->
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
  <div class="p-8 bg-white rounded-lg shadow-md w-96">
    <h1 class="mb-6 text-2xl font-bold text-center">Iniciar Sesión en TecnoWeb</h1>

    <!-- Formulario de Login Tradicional -->
    <form method="POST" action="{{ route('login.post') }}" class="mb-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
            <input type="email" id="email" name="email" required
                   class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
            <input type="password" id="password" name="password" required
                   class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full px-4 py-2 text-white transition duration-300 bg-green-600 rounded-md hover:bg-green-700">
            Iniciar Sesión
        </button>
    </form>

    <div class="relative flex items-center py-5">
        <div class="flex-grow border-t border-gray-300"></div>
        <span class="flex-shrink mx-4 text-gray-400">O</span>
        <div class="flex-grow border-t border-gray-300"></div>
    </div>

    <!-- Botón para iniciar el flujo OAuth (abre una ventana emergente) -->
    <button onclick="openAuthWindow()"
       class="flex items-center justify-center w-full px-4 py-2 text-white transition duration-300 bg-blue-600 rounded-md hover:bg-blue-700">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
        </svg>
        Iniciar Sesión con TecnoWeb (OAuth)
    </button>
  </div>

  <!-- Manejo de mensajes de sesión y errores (Toastr) -->
  @if (session('error'))
    <script>
      toastr.error("{{ session('error') }}", "Error");
    </script>
  @endif
  @if (session('success'))
    <script>
      toastr.success("{{ session('success') }}", "Éxito");
    </script>
  @endif
  @if (session('info'))
    <script>
      toastr.info("{{ session('info') }}", "Información");
    </script>
  @endif
  @if ($errors->any())
    @foreach ($errors->all() as $error)
      <script>
        toastr.error("{{ $error }}", "Error de Validación");
      </script>
    @endforeach
  @endif

  <script>
    // Configuración global de Toastr
    toastr.options = {
      "closeButton": true,
      "debug": false,
      "newestOnTop": true,
      "progressBar": true,
      "positionClass": "toast-top-right",
      "preventDuplicates": true,
      "onclick": null,
      "showDuration": "300",
      "hideDuration": "1000",
      "timeOut": "5000",
      "extendedTimeOut": "1000",
      "showEasing": "swing",
      "hideEasing": "linear",
      "showMethod": "fadeIn",
      "hideMethod": "fadeOut"
    };

    // Función para abrir la ventana de autenticación OAuth
    function openAuthWindow() {
        const width = 500;
        const height = 600;
        const left = (window.innerWidth - width) / 2;
        const top = (window.innerHeight - height) / 2;

        const authWindow = window.open(
            '{{ route("auth.initiate") }}',
            'TecnoWeb Login',
            `width=${width},height=${height},left=${left},top=${top}`
        );

        // Listener para mensajes de la ventana emergente (desde el callback)
        window.addEventListener('message', function(event) {
            // Asegúrate de que el origen del mensaje es de tu propio dominio por seguridad
            if (event.origin !== "{{ config('app.url') }}") {
                return;
            }

            if (event.data.type === 'AUTH_SUCCESS') {
                // Guardar el token y la información del usuario
                localStorage.setItem('access_token', event.data.token); // El token es la cadena directamente
                localStorage.setItem('user', JSON.stringify(event.data.user));

                // Mostrar mensaje de éxito
                toastr.success('Inicio de sesión exitoso');

                // Redirigir al dashboard/home de la aplicación principal
                window.location.href = '/home';
            } else if (event.data.error) {
                toastr.error(event.data.error);
            }
        });
    }
  </script>
</body>
</html>
```

### 5.2 `tecno-ward/resources/views/home.blade.php`

Una página simple para confirmar el login exitoso y mostrar el token.

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - TecnoWeb</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="p-8 text-center bg-white rounded-lg shadow-md w-96">
        <h1 class="mb-4 text-2xl font-bold">¡Inicio de Sesión Exitoso!</h1>
        <p class="text-gray-700">Has accedido a la página de inicio.</p>
        <div class="mt-6">
            <a href="{{ route('logout') }}" class="px-4 py-2 text-white transition duration-300 bg-red-500 rounded-md hover:bg-red-600">Cerrar Sesión</a>
        </div>
        <div class="p-3 mt-4 text-sm text-left break-all bg-gray-100 rounded" id="token-display" style="display: none;">
            <p class="font-semibold">Tu Token de Acceso:</p>
            <code class="block p-2 mt-2 bg-gray-200 rounded" id="access-token-value"></code>
        </div>
    </div>

    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        const urlParams = new URLSearchParams(window.location.search);
        const tokenFromUrl = urlParams.get('token');

        if (tokenFromUrl) {
            localStorage.setItem('access_token', tokenFromUrl);
            toastr.success('Token de acceso guardado. ¡Bienvenido!', 'Éxito');
            history.replaceState(null, '', window.location.pathname); // Limpiar el token de la URL
        }

        const storedToken = localStorage.getItem('access_token');
        if (storedToken) {
            document.getElementById('token-display').style.display = 'block';
            document.getElementById('access-token-value').textContent = storedToken;
            toastr.info('Ya has iniciado sesión.', 'Información');
        } else {
            toastr.warning('No se encontró un token de acceso.', 'Advertencia');
        }
    </script>
</body>
</html>
```

### 5.3 `tecno-ward/resources/views/vendor/passport/authorize.blade.php`

La página de autorización rediseñada.

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }} - Solicitud de Autorización</title>

    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex items-center justify-center h-screen font-sans bg-gray-100">
    <div class="w-full max-w-md p-8 text-center bg-white rounded-lg shadow-xl">
        <h1 class="mb-6 text-3xl font-extrabold text-gray-800">Solicitud de Autorización</h1>

        <!-- Introduction -->
        <p class="mb-6 text-lg text-gray-700">
            <strong class="text-blue-600">{{ $client->name }}</strong>
            está solicitando permiso para acceder a tu cuenta.
        </p>

        <!-- Scope List -->
        @if (count($scopes) > 0)
            <div class="p-4 mb-8 rounded-lg bg-blue-50">
                <p class="mb-3 font-semibold text-blue-800 text-md">Esta aplicación podrá:</p>
                <ul class="space-y-2 text-gray-700 list-disc list-inside">
                    @foreach ($scopes as $scope)
                        <li>{{ $scope->description }}</li>
                    @endforeach
                </ul>
            </div>
        @else
            <p class="mb-8 text-gray-600 text-md">Esta aplicación no solicita permisos específicos.</p>
        @endif

        <div class="flex justify-center space-x-4">
            <!-- Authorize Button -->
            <form method="post" action="{{ route('passport.authorizations.approve') }}">
                @csrf
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit"
                        class="px-6 py-3 font-semibold text-white transition duration-300 ease-in-out bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">
                    Autorizar
                </button>
            </form>

            <!-- Cancel Button -->
            <form method="post" action="{{ route('passport.authorizations.deny') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit"
                        class="px-6 py-3 font-semibold text-gray-800 transition duration-300 ease-in-out bg-gray-300 rounded-lg shadow-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-75">
                    Cancelar
                </button>
            </form>
        </div>
    </div>
</body>
</html>
```

## 6. Implementación en la Aplicación Cliente (Microservicio de Negocio)

Tu "API de Negocio" (o cualquier otra aplicación cliente) será la que utilice este microservicio de autenticación para obtener tokens y luego consumir sus propias rutas protegidas.

### 6.1 Configuración de la Aplicación Cliente

En tu aplicación cliente (por ejemplo, `negocio`), no necesitas Passport instalado a menos que también actúe como un servidor OAuth. Solo necesitas un cliente HTTP (como Guzzle) para manejar las redirecciones y las solicitudes.

### 6.2 Flujo de Autenticación en la Aplicación Cliente

1.  **Redirección para iniciar sesión:**
    Cuando un usuario necesita iniciar sesión en tu aplicación cliente, lo rediriges a la URL de login de tu microservicio de autenticación:
    ```php
    // En tu aplicación cliente (ej. un controlador Laravel o frontend JS)
    return redirect('http://localhost:8000/login'); // URL de tu microservicio de autenticación
    ```

2.  **Manejo del Callback (en la aplicación cliente):**
    Una vez que el usuario se autentica en el microservicio y autoriza la aplicación, el microservicio redirigirá a tu aplicación cliente a una URL de callback que tú especifiques (la `redirect_uri` que configuraste en Passport).

    ```php
    // Ejemplo de un controlador en tu aplicación cliente para manejar el callback
    use Illuminate\Http\Request;

    class AuthClientController extends Controller
    {
        public function handleProviderCallback(Request $request)
        {
            if ($request->has('token')) {
                $accessToken = $request->input('token');
                // Aquí guardas el $accessToken en la sesión, localStorage (para SPA), etc.
                // Rediriges al dashboard de tu aplicación cliente
                return redirect('/dashboard')->with('access_token', $accessToken);
            }

            // Manejo de errores si no se recibe el token
            return redirect('/login')->with('error', 'No se pudo obtener el token de acceso.');
        }
    }
    ```
    Asegúrate de que la `redirect_uri` configurada para el cliente de Passport en tu microservicio apunte a esta ruta de callback en tu aplicación cliente.

3.  **Consumo de APIs protegidas (en la aplicación cliente):**
    Una vez que tienes el `access_token`, tu aplicación cliente puede usarlo para hacer solicitudes autenticadas a tu API de negocio.

    ```php
    // Ejemplo de cómo consumir una API protegida desde tu aplicación cliente
    use GuzzleHttp\Client;

    $client = new Client();
    $response = $client->get('http://tu-api-negocio.com/api/ruta-protegida', [
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken, // Usa el token obtenido
        ],
    ]);

    $data = json_decode($response->getBody(), true);
    // ... procesar los datos de la API ...
    ```

## 7. Consideraciones Adicionales

*   **HTTPS:** En un entorno de producción, es **absolutamente crucial** que tanto tu microservicio de autenticación como tus APIs de negocio utilicen HTTPS para proteger las credenciales y los tokens.
*   **CORS:** Si tus aplicaciones cliente están en dominios diferentes a tu microservicio de autenticación, necesitarás configurar CORS (Cross-Origin Resource Sharing) en tu microservicio para permitir las solicitudes desde esos dominios. Laravel tiene un middleware CORS que puedes configurar.
*   **Gestión de Sesiones:** El microservicio de autenticación manejará su propia sesión web para el login tradicional. Las APIs de negocio no mantendrán sesiones de usuario, sino que se basarán en los tokens de acceso para la autenticación en cada solicitud.
*   **Validación de Tokens:** En tu API de negocio, asegúrate de que las rutas estén protegidas por el middleware `auth:api` (o `auth:passport` si lo configuras así) para que Laravel Passport valide los tokens de acceso entrantes. 