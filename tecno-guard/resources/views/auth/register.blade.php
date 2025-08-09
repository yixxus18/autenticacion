@extends('layouts.app')

@section('content')
<div class="auth-bg">
    <div class="auth-container">
        {{-- Panel izquierdo con el logo --}}
        <div class="auth-left">
            <img src="{{ asset('images/logo_2_sin_fondo.png') }}" alt="Logo" class="auth-logo">
        </div>

        {{-- Panel derecho con el formulario --}}
        <div class="auth-right">
            <h2 class="auth-title">Crear Cuenta</h2>
            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="auth-form">
                @csrf

                <div class="mb-2">
                    <label for="name" class="auth-label">Nombre Completo</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-regular fa-user"></i></span>
                        <input id="name" type="text" class="auth-input @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Nombre Apellido">
                    </div>
                </div>

                <div class="mb-2">
                    <label for="email" class="auth-label">Email</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-regular fa-envelope"></i></span>
                        <input id="email" type="email" class="auth-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="example@gmail.com">
                    </div>
                </div>

                <div class="mb-2">
                    <label for="password" class="auth-label">Contraseña</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-solid fa-key"></i></span>
                        <input id="password" type="password" class="auth-input @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="*****************">
                    </div>
                </div>

                <div class="mb-2">
                    <label for="password-confirm" class="auth-label">Confirmar Contraseña</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-solid fa-key"></i></span>
                        <input id="password-confirm" type="password" class="auth-input" name="password_confirmation" required autocomplete="new-password" placeholder="*****************">
                    </div>
                </div>

                <div class="mb-2">
                    <label for="phone" class="auth-label">Teléfono</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-solid fa-phone"></i></span>
                        <input id="phone" type="tel" class="auth-input @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="5555555555" pattern="\d{10}" maxlength="10">
                    </div>
                </div>

                <div class="mb-2">
                    <label for="ine_front" class="auth-label">Credencial INE (Frente)</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-regular fa-id-card"></i></span>
                        <input id="ine_front" type="file" class="auth-input file @error('ine_front') is-invalid @enderror" name="ine_front" required accept="image/*">
                    </div>
                    <small class="text-muted">Sube una imagen clara del frente de tu INE</small>
                </div>

                <div class="mb-3">
                    <label for="direccion" class="auth-label">Dirección (como aparece en tu INE)</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                        <input id="direccion" type="text" class="auth-input @error('direccion') is-invalid @enderror" name="direccion" value="{{ old('direccion') }}" required placeholder="Calle, Número, Colonia, Ciudad, Estado">
                    </div>
                </div>

                <div class="auth-buttons">
                    <button type="submit" class="auth-btn" id="register-btn">
                        <span id="register-btn-text">Registrarse</span>
                        <span id="register-loader" class="spinner-border d-none" role="status" aria-hidden="true"></span>
                    </button>
                    <a href="{{ route('login') }}" class="auth-btn secondary">Iniciar Sesión</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Lógica para el loader del botón ---
    const form = document.querySelector('form[action="{{ route('register') }}"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const btn = document.getElementById('register-btn');
            const loader = document.getElementById('register-loader');
            const btnText = document.getElementById('register-btn-text');

            btn.disabled = true;
            loader.classList.remove('d-none');
            btnText.style.visibility = 'hidden';
        });
    }

    // --- Lógica de Notificaciones Toast ---
    function showToast(message, isSuccess = true) {
        let toastContainer = document.getElementById('toast-container-main');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container-main';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1055';
            document.body.appendChild(toastContainer);
        }

        const toastId = 'toast-' + Date.now();
        const toastBg = isSuccess ? 'bg-success' : 'bg-danger';
        const icon = isSuccess ? '✅' : '❌';

        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white ${toastBg} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <span style="font-size:1.1em; margin-right: 8px;">${icon}</span>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();
        toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
    }

    // Mostrar errores de validación del backend
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            showToast("{{ $error }}", false);
        @endforeach
    @endif
});
</script>

{{-- Estilos Comunes de Autenticación --}}
<style>
    :root {
        --auth-bg-color: #d6f8e3;
        --auth-panel-color: #8fcabb;
        --auth-white-color: #fff;
    }
    body > main.py-4 { padding: 0 !important; }

    .auth-bg {
        background-color: var(--auth-bg-color);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .auth-container {
        display: flex;
        max-width: 900px;
        width: 100%;
        background: var(--auth-white-color);
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    .auth-left {
        background-color: var(--auth-panel-color);
        width: 45%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
    }
    .auth-logo {
        max-width: 180px;
        width: 100%;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
    }
    .auth-right {
        width: 55%;
        padding: 1.5rem 3rem; /* Menos padding vertical para que quepa todo */
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .auth-form { width: 100%; }
    .auth-title {
        font-weight: 700;
        font-size: 2rem;
        color: #333;
        margin-bottom: 1rem;
        text-align: center;
    }
    .auth-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 0.25rem;
    }
    .auth-input-group {
        display: flex;
        align-items: center;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    .auth-input-icon {
        padding: 0.5rem 0.75rem;
        color: #888;
        font-size: 1.1rem;
    }
    .auth-input {
        border: none;
        outline: none;
        box-shadow: none;
        width: 100%;
        padding: 0.5rem 0.75rem;
        padding-left: 0;
        background: transparent;
    }
    .auth-input.file { padding: 0.35rem 0.75rem; }
    .auth-buttons {
        margin-top: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .auth-btn {
        width: 100%;
        background: var(--auth-panel-color);
        color: var(--auth-white-color);
        border: none;
        border-radius: 0.375rem;
        padding: 0.75rem;
        font-size: 1rem;
        font-weight: 600;
        transition: background-color 0.3s ease;
        position: relative;
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    .auth-btn:hover {
        background-color: #7bb2a6;
        color: var(--auth-white-color);
    }
    .auth-btn .spinner-border {
        width: 1.25rem;
        height: 1.25rem;
        position: absolute;
    }
    .auth-btn.secondary {
        background-color: #f0f0f0;
        color: #555;
        border: 1px solid #ddd;
    }
    .auth-btn.secondary:hover {
        background-color: #e0e0e0;
    }
    @media (max-width: 768px) {
        .auth-container { flex-direction: column; }
        .auth-left, .auth-right { width: 100%; }
        .auth-right { padding: 2rem 1.5rem; }
    }
</style>
@endpush
