<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticación Completada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #d6f8e3;
            margin: 0;
            text-align: center;
        }
        .message-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(143,202,187,0.15);
            max-width: 500px;
            width: 90%;
            border: 2px solid #8fcabb;
        }
        h1 {
            color: #8fcabb;
            margin-bottom: 20px;
            font-size: 2em;
            font-weight: bold;
        }
        p {
            color: #333;
            font-size: 1.1em;
        }
        a {
            color: #8fcabb;
            text-decoration: underline;
            font-weight: 500;
        }
        a:hover {
            color: #5e8e7a;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <h1>¡Autenticación Completada!</h1>
        <p>Estás siendo redirigido a la aplicación principal...</p>
        @if(isset($redirect_url))
        <p>Si la redirección no ocurre automáticamente, haz clic <a href="{{ $redirect_url }}">aquí</a>.</p>
        @endif
    </div>

    <script>
        @if(isset($redirect_url))
        setTimeout(function() {
            window.location.href = '{{ $redirect_url }}';
        }, 3000);
        @endif
    </script>
</body>
</html>
