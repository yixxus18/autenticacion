<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de Cuenta - Tecno Guard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #d6f8e3;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #8fcabb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 12px 12px 0 0;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #8fcabb;
            border-radius: 0 0 12px 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #8fcabb;
            color: #fff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 1.1em;
        }
        .button:hover {
            background-color: #6ea89e;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            background: #d6f8e3;
            border-radius: 0 0 12px 12px;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 5px;
            color: #8fcabb;
            margin: 20px 0;
            padding: 15px;
            background-color: #fff;
            border: 2px dashed #8fcabb;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido a Tecno Guard!</h1>
        </div>

        <div class="content">
            <p>Hola,</p>

            <p>Gracias por registrarte en Tecno Guard. Para activar tu cuenta y comenzar a usar nuestros servicios, por favor haz clic en el siguiente botón:</p>

            <div style="text-align: center;">
                <a href="{{ $signedroute }}" class="button">Activar mi cuenta</a>
            </div>

            <p>Si el botón no funciona, puedes copiar y pegar el siguiente enlace en tu navegador:</p>
            <p style="word-break: break-all; color: #666;">{{ $signedroute }}</p>

            <p><strong>Importante:</strong> Este enlace expirará en 10 minutos por razones de seguridad.</p>

            <p>Si no solicitaste esta cuenta, puedes ignorar este correo.</p>
        </div>

        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
            <p>&copy; {{ date('Y') }} Tecno Guard. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
