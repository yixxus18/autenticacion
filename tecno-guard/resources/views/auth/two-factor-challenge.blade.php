@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Autenticación de Dos Factores') }}</div>

                <div class="card-body">
                    <p>{{ __('Por favor, ingresa el código de verificación que hemos enviado a tu correo electrónico.') }}</p>

                    <form method="POST" action="{{ route('2fa.verify') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="two_factor_code" class="col-md-4 col-form-label text-md-end">{{ __('Código de Verificación') }}</label>

                            <div class="col-md-6">
                                <input id="two_factor_code" type="text" class="form-control @error('two_factor_code') is-invalid @enderror" name="two_factor_code" required autofocus>

                                @error('two_factor_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Verificar') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
