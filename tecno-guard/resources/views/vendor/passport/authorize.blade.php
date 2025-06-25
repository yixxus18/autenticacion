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
        :root {
            --primary-color: #139BFF;
            --secondary-color: #50B5FF;
            --background-color: #EAEBE7;
            --third-color: #0061A9;
            --white-color: #FFFFFF;
            --black-color: #000000;
            --gray-color: #848484;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            background-color: var(--white-color);
        }
        .card-header {
            background: var(--primary-color);
            color: var(--white-color);
            border-radius: 1rem 1rem 0 0;
            font-weight: 600;
            text-align: center;
            font-size: 1.2rem;
            border-bottom: none;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: var(--third-color);
            border-color: var(--third-color);
        }
        .btn-link {
            color: var(--primary-color);
        }
        .btn-link:hover {
            color: var(--third-color);
        }
        .passport-logo {
            display: block;
            margin: 2rem auto 1rem auto;
            max-width: 160px;
        }
        .scopes {
            margin-top: 20px;
        }
        .buttons {
            margin-top: 25px;
            text-align: center;
        }
        .btn {
            width: 140px;
        }
        .btn-approve {
            margin-right: 15px;
        }
        .card-body p, .card-body ul {
            color: var(--black-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6 col-xl-5">
                <div class="card card-depth mt-4">
                    <img src="{{ asset('images/tecno-guard-logo.png') }}" alt="Tecno Guard Logo" class="passport-logo">
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
