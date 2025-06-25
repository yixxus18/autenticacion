@extends('layouts.app')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="row w-100 justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm p-4 text-center">
                <h2 class="fw-bold mb-4">Codigo</h2>
                <form method="POST" action="{{ route('2fa.verify') }}">
                    @csrf
                    <div class="mb-4 text-start">
                        <label for="two_factor_code" class="form-label fw-bold">Codigo de verificación</label>
                        <div class="input-group custom-input-icon">
                            <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/key_icon_black.svg') }}" alt="icono llave" style="width:22px;height:22px;"></span>
                            <input id="two_factor_code" type="text" class="form-control @error('two_factor_code') is-invalid @enderror border-start-0" name="two_factor_code" required autofocus placeholder="000000">
                            @error('two_factor_code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 position-relative" id="twofa-btn">
                        <span id="twofa-btn-text">Aceptar</span>
                        <span id="twofa-loader" class="spinner-border spinner-border-sm text-light position-absolute top-50 start-50 translate-middle d-none" role="status" aria-hidden="true"></span>
                    </button>
                </form>
            </div>
        </div>
        <div class="col-md-6 d-flex flex-column align-items-center justify-content-center p-4">
            <img src="{{ asset('images/tecno-guard-logo.png') }}" alt="Tecno Guard Logo" class="img-fluid mb-4" style="max-width: 300px;">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action=\"{{ route('2fa.verify') }}\"]');
        const btn = document.getElementById('twofa-btn');
        const loader = document.getElementById('twofa-loader');
        const btnText = document.getElementById('twofa-btn-text');
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
    #twofa-loader.spinner-border {
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
