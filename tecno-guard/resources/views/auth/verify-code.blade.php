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
            <h2 class="auth-title">Verificar código</h2>
            <p class="text-center mb-4">Hemos enviado un código de verificación a<br><strong>{{ session('email') }}</strong></p>

            <form method="POST" action="{{ route('password.verify') }}" class="auth-form">
                @csrf
                <input type="hidden" name="email" value="{{ session('email') }}">

                {{-- Campo Código --}}
                <div class="mb-4">
                    <label for="code" class="auth-label">Código de verificación</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-solid fa-shield-halved"></i></span>
                        <input id="code" type="text" class="auth-input @error('code') is-invalid @enderror"
                               name="code" required autofocus placeholder="123456">
                    </div>
                </div>

                {{-- Botón de Verificar --}}
                <button type="submit" class="auth-btn" id="verify-btn">
                    <span id="btn-text">Verificar código</span>
                    <span id="btn-loader" class="spinner-border d-none" role="status" aria-hidden="true"></span>
                </button>

                <div class="text-center mt-3">
                    <p id="resend-text">¿No recibiste el código? <a href="{{ route('password.resend') }}" id="resend-link" class="auth-link">Reenviar código</a></p>
                    <p id="countdown" class="d-none">Puedes solicitar otro código en <span id="timer">60</span> segundos</p>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar el loader del botón
    const form = document.querySelector('form[action="{{ route('password.verify') }}"]');
    if (form) {
        form.addEventListener('submit', function() {
            const btn = document.getElementById('verify-btn');
            const loader = document.getElementById('btn-loader');
            const btnText = document.getElementById('btn-text');

            btn.disabled = true;
            loader.classList.remove('d-none');
            btnText.style.visibility = 'hidden';
        });
    }

    // Contador para reenviar código
    let countdown = 60;
    const resendLink = document.getElementById('resend-link');
    const resendText = document.getElementById('resend-text');
    const countdownEl = document.getElementById('countdown');
    const timerEl = document.getElementById('timer');

    if (resendLink) {
        resendLink.addEventListener('click', function(e) {
            e.preventDefault();
            resendText.classList.add('d-none');
            countdownEl.classList.remove('d-none');

            const interval = setInterval(() => {
                countdown--;
                timerEl.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(interval);
                    countdownEl.classList.add('d-none');
                    resendText.classList.remove('d-none');
                    countdown = 60;
                }
            }, 1000);

            // Simular envío del código
            fetch(this.href)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Código reenviado correctamente', true);
                    }
                });
        });
    }
});
</script>
@endpush
@endsection
