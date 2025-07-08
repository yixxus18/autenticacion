<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Código de Autenticación de Dos Factores</title>
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
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            background: #d6f8e3;
            border-radius: 0 0 12px 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Código de Verificación</h1>
    </div>

    <div class="content">
        <p>Hola {{ $user->name }},</p>

        <p>Has solicitado iniciar sesión en tu cuenta. Para completar el proceso, utiliza el siguiente código de verificación:</p>

        <div class="code">
            {{ $code }}
        </div>

        <p>Este código expirará en 10 minutos por razones de seguridad.</p>

        <p>Si no has intentado iniciar sesión, por favor ignora este correo o contacta con soporte si tienes alguna preocupación.</p>
    </div>

    <div class="footer">
        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
        <p>&copy; {{ date('Y') }} TecnoGuard. Todos los derechos reservados.</p>
    </div>
</body>
</html>
