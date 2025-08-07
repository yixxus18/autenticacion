<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {
            if ($request->wantsJson()) {
                return $this->handleApiException($e);
            }
        });
    }

    protected function handleApiException(Throwable $e)
    {
        $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 500;
        $response = [
            'status' => false,
            'message' => 'Error',
            'error' => 'server_error',
            'data' => null
        ];

        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }

        switch (true) {
            case $e instanceof ValidationException:
                $statusCode = 422;
                $response['message'] = 'Los datos proporcionados no son válidos.';
                $response['error'] = 'validation_failed';
                $response['data'] = ['errors' => $e->errors()];
                break;

            case $e instanceof AuthenticationException:
                $statusCode = 401;
                $response['message'] = 'No autenticado.';
                $response['error'] = 'unauthenticated';
                break;

            case $e instanceof AuthorizationException:
                $statusCode = 403;
                $response['message'] = 'No tienes permiso para realizar esta acción.';
                $response['error'] = 'unauthorized';
                break;

            case $e instanceof ModelNotFoundException:
                $statusCode = 404;
                $response['message'] = 'El recurso solicitado no se pudo encontrar.';
                $response['error'] = 'not_found';
                break;

            case $e instanceof NotFoundHttpException:
                $statusCode = 404;
                $response['message'] = 'La ruta solicitada no se pudo encontrar.';
                $response['error'] = 'not_found';
                break;
            
            case $e instanceof HttpException:
                $statusCode = $e->getStatusCode();
                $response['message'] = $e->getMessage() ?: 'Error en la solicitud.';
                $response['error'] = 'http_exception';
                break;

            default:
                $statusCode = 500;
                $response['message'] = 'Ocurrió un error inesperado en el servidor.';
                $response['error'] = 'server_error';
                break;
        }
        
        // Ensure message is set even if not explicitly defined in a case
        if ($response['message'] === 'Error' && !empty($e->getMessage()) && !($e instanceof HttpException)) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response, $statusCode);
    }
}
