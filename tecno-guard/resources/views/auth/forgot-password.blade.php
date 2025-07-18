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
            <h2 class="auth-title">Restablecer contraseña</h2>
            <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                @csrf

                {{-- Campo Email --}}
                <div class="mb-4">
                    <label for="email" class="auth-label">Email registrado</label>
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="fa-regular fa-envelope"></i></span>
                        <input id="email" type="email" class="auth-input @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autocomplete="email"
                               placeholder="example@gmail.com">
                    </div>
                </div>

                {{-- Botón de Enviar --}}
                <button type="submit" class="auth-btn" id="submit-btn">
                    <span id="btn-text">Enviar código</span>
                    <span id="btn-loader" class="spinner-border d-none" role="status" aria-hidden="true"></span>
                </button>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="auth-link">
                        <i class="fas fa-arrow-left me-1"></i> Volver al login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ route('password.email') }}"]');
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
