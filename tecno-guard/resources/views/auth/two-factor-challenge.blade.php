@extends('layouts.app')

@section('content')
<div class="auth-bg">
    <div class="verify-container">

        <img src="{{ asset('images/logo_2_sin_fondo.png') }}" alt="Logo" class="verify-logo">

        <h2 class="verify-title">Código de verificación</h2>

        <form method="POST" action="{{ route('2fa.verify') }}">
            @csrf

            <div class="mb-4">
                <div class="auth-input-group">
                    <span class="auth-input-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    <input id="two_factor_code" type="text" class="auth-input text-center @error('two_factor_code') is-invalid @enderror" name="two_factor_code" required autofocus placeholder="000000" maxlength="6" style="letter-spacing: 0.5rem; font-size: 1.2rem;">
                </div>
            </div>

            <button type="submit" class="auth-btn" id="twofa-btn">
                <span id="twofa-btn-text">Aceptar</span>
                <span id="twofa-loader" class="spinner-border d-none" role="status" aria-hidden="true"></span>
            </button>
        </form>

        <div class="verify-footer">
            <span>@ 2025, by Tecno Team :)</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Lógica para el loader del botón ---
    const form = document.querySelector('form[action="{{ route('2fa.verify') }}"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const btn = document.getElementById('twofa-btn');
            const loader = document.getElementById('twofa-loader');
            const btnText = document.getElementById('twofa-btn-text');

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
    @error('two_factor_code')
        showToast("{{ $message }}", false);
    @enderror

    // Mostrar errores generales de la sesión
    @if(session('error'))
        showToast("{{ session('error') }}", false);
    @endif
});
</script>

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

    .verify-container {
        background: var(--auth-white-color);
        padding: 2.5rem;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 420px;
        text-align: center;
    }
    .verify-logo {
        max-width: 100px;
        margin-bottom: 1.5rem;
    }
    .verify-title {
        font-weight: 700;
        font-size: 1.8rem;
        color: #333;
        margin-bottom: 2rem;
    }
    .verify-footer {
        margin-top: 2rem;
        color: #aaa;
        font-size: 0.9em;
    }

    .auth-input-group {
        display: flex;
        align-items: center;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    .auth-input-group:focus-within {
        border-color: var(--auth-panel-color);
        box-shadow: 0 0 0 0.25rem rgba(143, 202, 187, 0.4);
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
        padding: 0.75rem;
        padding-left: 0;
        background: transparent;
    }
    .auth-btn {
        width: 100%;
        background: var(--auth-panel-color);
        color: var(--auth-white-color);
        border: none;
        border-radius: 0.375rem;
        padding: 0.75rem;
        font-size: 1.1rem;
        font-weight: 600;
        position: relative;
        min-height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .auth-btn:hover {
        background-color: #7bb2a6;
    }
    .auth-btn .spinner-border {
        width: 1.5rem;
        height: 1.5rem;
        position: absolute;
    }
</style>
@endpush
