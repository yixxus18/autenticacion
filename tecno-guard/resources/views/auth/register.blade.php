@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header text-center fs-4 fw-bold bg-white border-0">
                            {{ __('Crear Cuenta') }}
                        </div>

                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('Nombre Completo') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/account_circle_icon_black.svg') }}" alt="icono nombre" style="width:22px;height:22px;"></span>
                                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror border-start-0" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Juan Pérez" minlength="10" maxlength="127">
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('Email') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/mail_icon_black.svg') }}" alt="icono email" style="width:22px;height:22px;"></span>
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror border-start-0" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="example@gmail.com" minlength="10" maxlength="127">
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">{{ __('Contraseña') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/key_icon_black.svg') }}" alt="icono contraseña" style="width:22px;height:22px;"></span>
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror border-start-0" name="password" required autocomplete="new-password" placeholder="***********" minlength="9">
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password-confirm" class="form-label">{{ __('Confirmar Contraseña') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/key_icon_black.svg') }}" alt="icono confirmar contraseña" style="width:22px;height:22px;"></span>
                                        <input id="password-confirm" type="password" class="form-control border-start-0" name="password_confirmation" required autocomplete="new-password" placeholder="***********">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">{{ __('Teléfono') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/phone_icon_black.svg') }}" alt="icono teléfono" style="width:22px;height:22px;"></span>
                                        <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror border-start-0" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="5555555555" pattern="\d{10}" maxlength="10" minlength="10">
                                        @error('phone')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="ine_front" class="form-label">{{ __('Foto de INE (Frente)') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/camera_icon_black.svg') }}" alt="icono INE" style="width:22px;height:22px;"></span>
                                        <input id="ine_front" type="file" class="form-control @error('ine_front') is-invalid @enderror border-start-0" name="ine_front" required>
                                        @error('ine_front')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg position-relative" id="register-btn">
                                        <span id="register-btn-text">{{ __('Registrarse') }}</span>
                                        <span id="register-loader" class="spinner-border spinner-border-sm text-light position-absolute top-50 start-50 translate-middle d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                        {{ __('Iniciar Sesión') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 d-flex flex-column align-items-center justify-content-center p-4">
                    <img src="{{ asset('images/tecno-guard-logo.png') }}" alt="Tecno Guard Logo" class="img-fluid mb-4" style="max-width: 300px;">
                    <div class="card p-3 text-center border-0 shadow-sm">
                        <p class="mb-0 text-muted">Asegúrate que la imagen de tu INE sea clara.</p>
                        <p class="mb-0 text-muted mt-2">La autenticación de dos factores es obligatoria para mayor seguridad</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action="{{ route('register') }}"]');
        const btn = document.getElementById('register-btn');
        const loader = document.getElementById('register-loader');
        const btnText = document.getElementById('register-btn-text');
        // Mensajes de error y éxito (solo backend)
        if(document.getElementById('alert-toast')) document.getElementById('alert-toast').remove();
        if(document.getElementById('alert-toast-success')) document.getElementById('alert-toast-success').remove();
        let errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger mt-2 d-none';
        errorDiv.id = 'alert-toast';
        document.body.appendChild(errorDiv);
        let successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success mt-2 d-none';
        successDiv.id = 'alert-toast-success';
        document.body.appendChild(successDiv);
        function showError(msg) {
            errorDiv.innerHTML = `<span style='font-size:1.3em;vertical-align:middle;'>❌</span> <span>${msg}</span><button type='button' class='btn-close float-end' onclick='this.parentNode.classList.add("d-none")'></button>`;
            errorDiv.className = 'alert alert-danger alert-toast show';
            setTimeout(()=>{ errorDiv.classList.add('d-none'); }, 5000);
        }
        function showSuccess(msg) {
            successDiv.innerHTML = `<span style='font-size:1.3em;vertical-align:middle;'>✅</span> <span>${msg}</span><button type='button' class='btn-close float-end' onclick='this.parentNode.classList.add("d-none")'></button>`;
            successDiv.className = 'alert alert-success alert-toast alert-toast-success show';
            setTimeout(()=>{ successDiv.classList.add('d-none'); }, 5000);
        }
        // Mostrar errores de validación de backend como toast
        @if ($errors->has('name'))
            showError(`{{ $errors->first('name') }}`);
        @endif
        @if ($errors->has('email'))
            showError(`{{ $errors->first('email') }}`);
        @endif
        @if ($errors->has('password'))
            showError(`{{ $errors->first('password') }}`);
        @endif
        @if ($errors->has('phone'))
            showError(`{{ $errors->first('phone') }}`);
        @endif
        @if ($errors->has('ine_front'))
            showError(`{{ $errors->first('ine_front') }}`);
        @endif
        // Mostrar mensajes de éxito del backend
        if(typeof window.successMessage !== 'undefined') {
            showSuccess(window.successMessage);
        }
        // Mostrar mensajes de error del backend
        if(typeof window.errorMessage !== 'undefined') {
            showError(window.errorMessage);
        }
        if(form) {
            form.addEventListener('submit', function(e) {
                btn.disabled = true;
                loader.classList.remove('d-none');
                btnText.classList.add('invisible');
            });
        }
    });
</script>
<style>
    #register-loader.spinner-border {
        color: var(--primary-color) !important;
    }
    .alert-toast {
        position: fixed;
        top: 30px;
        right: 30px;
        min-width: 320px;
        z-index: 9999;
        border-radius: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        font-size: 1.1em;
        display: flex;
        align-items: center;
        padding: 1em 1.5em;
        background: #f44336;
        color: #fff;
        border: none;
        transition: opacity 0.3s;
    }
    .alert-toast-success {
        background: #43c463 !important;
        color: #fff !important;
    }
    .alert-toast .btn-close {
        margin-left: auto;
        filter: invert(1);
    }
</style>
@endpush
