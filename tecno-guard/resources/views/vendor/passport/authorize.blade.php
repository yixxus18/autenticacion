<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Autorización</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .card {
            background-color: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(143,202,187,0.15);
            max-width: 500px;
            width: 90%;
            border: 2px solid #8fcabb;
        }
        .card-header {
            color: #8fcabb;
            margin-bottom: 20px;
            font-size: 2em;
            font-weight: bold;
            border-bottom: none;
            padding: 0;
        }
        .card-body p {
            color: #333;
            font-size: 1.1em;
        }
        .btn-primary {
            background-color: #8fcabb;
            border-color: #8fcabb;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #5e8e7a;
            border-color: #5e8e7a;
        }
        .btn-link {
            color: #8fcabb;
            text-decoration: underline;
            font-weight: 500;
            background: none;
            border: none;
            cursor: pointer;
        }
        .btn-link:hover {
            color: #5e8e7a;
            text-decoration: none;
        }
        .passport-logo {
            display: block;
            margin: 0 auto 20px auto;
            max-width: 200px;
        }
        .scopes ul {
            text-align: left;
            padding-left: 20px;
        }
        .buttons {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6 col-xl-5">
                <img src="{{ asset('images/tecno-guard-logo.png') }}" alt="Tecno Guard Logo" class="passport-logo">
                <div class="card card-depth mt-4">
                    <div class="card-header">
                        Solicitud de Autorización
                    </div>
                    <div class="card-body">
                        <p><strong>{{ $client->name }}</strong> solicita permiso para acceder a tu cuenta.</p>
                        @if (count($scopes) > 0)
                            <div class="scopes">
                                <p><strong>Esta aplicación podrá:</strong></p>
                                <ul>
                                    @foreach ($scopes as $scope)
                                        <li>{{ $scope->description }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="buttons">
                            <form method="post" action="{{ route('passport.authorizations.approve') }}">
                                @csrf
                                <input type="hidden" name="state" value="{{ $request->state }}">
                                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                                <button type="submit" class="btn btn-primary btn-approve">Autorizar</button>
                            </form>
                            <form method="post" action="{{ route('passport.authorizations.deny') }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="state" value="{{ $request->state }}">
                                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                                <button class="btn btn-link">Cancelar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
