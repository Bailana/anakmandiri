@extends('layouts/blankLayout')

@section('title', 'Lupa Password')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="position-relative">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6 mx-4">
            <!-- Logo -->
            <div class="card p-sm-7 p-2">
                <!-- Forgot Password -->
                <div class="app-brand justify-content-center mt-5">
                    <a href="{{ url('/') }}" class="app-brand-link gap-3">
                        <span class="app-brand-logo demo"><img src="{{ asset('assets/img/am.png') }}" alt="Logo" style="height:70px;"></span>
                    </a>
                </div>
                <!-- /Logo -->
                <div class="card-body mt-1">
                    <h4 class="mb-1">Lupa Password? 🔒</h4>
                    <p class="mb-5">Masukkan email Anda yang Terdaftar.</p>

                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif

                    <form id="formAuthentication" class="mb-5" action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <div class="form-floating form-floating-outline mb-5 form-control-validation">
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" autofocus required />
                            <label>Email</label>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100 mb-5">Kirim Reset Link</button>
                    </form>
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
                            <i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-20px me-1_5"></i>
                            Kembali ke Halaman Login
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Forgot Password -->
            {{-- Gambar auth-tree dan triangle-bg dihapus sesuai permintaan --}}
        </div>
    </div>
</div>
@endsection