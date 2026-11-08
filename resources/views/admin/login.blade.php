@extends('layouts.admin')
@section('title', __('Sign in').' · Rounder')

@section('body')
<style>
.login-wrapper {
    min-height: 100vh;
    background: radial-gradient(circle at 50% 25%, rgba(2, 89, 104, 0.45) 0%, transparent 65%),
                radial-gradient(circle at 80% 80%, rgba(251, 130, 47, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, #011d24 0%, #02333E 50%, #01161b 100%);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 1.5rem 1rem;
    position: relative;
}
.login-topbar {
    display: flex;
    justify-content: flex-end;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}
.login-lang-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(221, 243, 238, 0.2);
    color: #fff;
    border-radius: 50rem;
    padding: 0.38rem 0.95rem;
    font-size: 0.82rem;
    font-weight: 500;
    transition: all .18s ease;
}
.login-lang-btn:hover {
    background: rgba(255, 255, 255, 0.16);
    border-color: rgba(221, 243, 238, 0.35);
    color: #fff;
}
.login-card {
    width: 100%;
    max-width: 420px;
    margin: auto;
    background: #ffffff;
    border-radius: 26px;
    padding: 3rem 2.2rem 2.2rem;
    box-shadow: 0 25px 70px rgba(1, 29, 36, 0.5), 0 4px 20px rgba(2, 51, 62, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.85);
    position: relative;
    margin-top: 3.5rem;
}
.login-brand-emblem-wrap {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    justify-content: center;
    z-index: 10;
}
.login-brand-emblem {
    background: linear-gradient(145deg, #02333E 0%, #024554 60%, #01242C 100%);
    border: 4px solid #ffffff;
    box-shadow: 0 10px 28px rgba(1, 36, 44, 0.35);
    border-radius: 20px;
    padding: 0.65rem 1.7rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.login-brand-emblem .brand-text {
    font-size: 2.15rem;
    letter-spacing: -0.03em;
    line-height: 1;
}
.login-input-wrap {
    position: relative;
}
.login-input-icon {
    position: absolute;
    inset-inline-start: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #8da4a8;
    font-size: 0.95rem;
    pointer-events: none;
    z-index: 2;
}
.login-input {
    padding-inline-start: 2.85rem !important;
    padding-inline-end: 2.85rem !important;
    height: 48px;
    border-radius: 12px !important;
    border: 1px solid #d8e4e0;
    background-color: #fafcfb;
    font-size: 0.92rem;
    transition: all .18s ease;
}
.login-input:focus {
    background-color: #ffffff;
    border-color: var(--orange) !important;
    box-shadow: 0 0 0 3.5px rgba(251, 130, 47, 0.18) !important;
}
.login-password-toggle {
    position: absolute;
    inset-inline-end: 0.65rem;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: #8da4a8;
    padding: 0.35rem 0.5rem;
    cursor: pointer;
    z-index: 2;
    transition: color .15s ease;
}
.login-password-toggle:hover {
    color: var(--petrol);
}
.login-submit-btn {
    height: 50px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1rem;
    background: var(--orange) !important;
    border-color: var(--orange) !important;
    color: #fff !important;
    box-shadow: 0 4px 18px rgba(251, 130, 47, 0.38);
    transition: all .2s ease;
}
.login-submit-btn:hover, .login-submit-btn:focus {
    background: var(--orange-hover) !important;
    border-color: var(--orange-hover) !important;
    box-shadow: 0 6px 22px rgba(251, 130, 47, 0.48);
    transform: translateY(-1px);
    color: #fff !important;
}
.login-footer {
    text-align: center;
    color: rgba(221, 243, 238, 0.6);
    font-size: 0.8rem;
    padding: 1rem 0 0.5rem;
}
</style>

<div class="login-wrapper">
    {{-- Top corner: language switcher only --}}
    <div class="login-topbar">
        <div class="dropdown">
            <button class="login-lang-btn d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-globe"></i>
                <span>{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                <i class="fa-solid fa-chevron-down small opacity-75"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1" style="min-width: 130px; border-radius: 12px;">
                <li>
                    <form method="post" action="{{ route('locale.update') }}">
                        @csrf
                        <input type="hidden" name="locale" value="ar">
                        <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ app()->getLocale() === 'ar' ? 'fw-bold text-success active' : '' }}">
                            <span>العربية</span>
                            @if(app()->getLocale() === 'ar')<i class="fa-solid fa-check small"></i>@endif
                        </button>
                    </form>
                </li>
                <li>
                    <form method="post" action="{{ route('locale.update') }}">
                        @csrf
                        <input type="hidden" name="locale" value="en">
                        <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ app()->getLocale() === 'en' ? 'fw-bold text-success active' : '' }}">
                            <span>English</span>
                            @if(app()->getLocale() === 'en')<i class="fa-solid fa-check small"></i>@endif
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    {{-- Center: Floating Brand Emblem Login Card --}}
    <div class="login-card">
        {{-- Floating Brand Emblem --}}
        <div class="login-brand-emblem-wrap">
            <div class="login-brand-emblem">
                <span class="brand-text text-rounder-orange">rounder</span>
            </div>
        </div>

        <div class="text-center mb-4 mt-2">
            <h1 class="h4 fw-bold text-dark mb-1">{{ __('Welcome back') }}</h1>
            <p class="text-secondary small mb-0">{{ __('Enter your credentials to access the admin portal') }}</p>
        </div>

        @include('admin.partials.feedback')

        <form method="post" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">{{ __('Email address or phone') }}</label>
                <div class="login-input-wrap">
                    <i class="fa-solid fa-envelope login-input-icon"></i>
                    <input id="email" name="email" type="text" placeholder="example@domain.com / 05XXXXXXXX" class="form-control login-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username">
                </div>
                @error('email')<div class="invalid-feedback d-block mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">{{ __('Password') }}</label>
                <div class="login-input-wrap">
                    <i class="fa-solid fa-lock login-input-icon"></i>
                    <input id="password" name="password" type="password" class="form-control login-input" placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="login-password-toggle" id="togglePasswordBtn" aria-label="Toggle password visibility">
                        <i class="fa-solid fa-eye-slash" id="togglePasswordIcon"></i>
                    </button>
                </div>
                @error('password')<div class="invalid-feedback d-block mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check m-0">
                    <input class="form-check-input" id="remember" name="remember" value="1" type="checkbox">
                    <label class="form-check-label small text-secondary" for="remember">{{ __('Remember me') }}</label>
                </div>
            </div>

            <button class="btn btn-primary w-100 login-submit-btn">
                <span>{{ __('Sign in') }}</span>
                <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }} ms-1"></i>
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top border-light">
            <span class="small text-muted d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                <i class="fa-solid fa-shield-halved text-success"></i>
                {{ __('Secure connection') }}
            </span>
        </div>
    </div>

    {{-- Bottom subtle copyright --}}
    <div class="login-footer">
        © {{ date('Y') }} {{ __('Rounder admin') }}
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const pwdInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const toggleIcon = document.getElementById('togglePasswordIcon');
    if (toggleBtn && pwdInput && toggleIcon) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
            toggleIcon.classList.toggle('fa-eye', isPassword);
            toggleIcon.classList.toggle('fa-eye-slash', !isPassword);
        });
    }
})();
</script>
@endpush


