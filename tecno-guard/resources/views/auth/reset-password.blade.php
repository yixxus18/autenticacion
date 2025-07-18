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
            <h2 class="auth-title">Nueva contraseña</h2>
            <p class="text-center mb-4">Ingresa y confirma tu nueva contraseña</p>

            <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                @csrf
                <input type="hidden" name="email" value="{{ session('email') }}">
                <input type="hidden" name="code" value="{{ session('code') }}">

                {{-- Campo Nueva Contraseña --}}
                <div class="mb-3">
                    <label for="password" class="auth-label">Nueva contraseña</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-solid fa-lock"></i></span>
                        <input id="password" type="password" class="auth-input @error('password') is-invalid @enderror"
                               name="password" required autocomplete="new-password" placeholder="********">
                    </div>
                </div>

                {{-- Campo Confirmar Contraseña --}}
                <div class="mb-4">
                    <label for="password-confirm" class="auth-label">Confirmar contraseña</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-solid fa-lock"></i></span>
                        <input id="password-confirm" type="password" class="auth-input"
                               name="password_confirmation" required autocomplete="new-password" placeholder="********">
                    </div>
                </div>

                {{-- Botón de Actualizar --}}
                <button type="submit" class="auth-btn" id="submit-btn">
                    <span id="btn-text">Actualizar contraseña</span>
                    <span id="btn-loader" class="spinner-border d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ route('password.update') }}"]');
    if (form) {
        form.addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            const loader = document.getElementById('btn-loader');
            const btnText = document.getElementById('btn-text');

            btn.disabled = true;
            loader.classList.remove('d-none');
            btnText.style.visibility = 'hidden';
        });
    }
});
</script>
@endpush
@endsection
