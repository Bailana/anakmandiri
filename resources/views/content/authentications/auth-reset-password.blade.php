@extends('layouts/blankLayout')

@section('title', 'Reset Password - Pages')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6 mx-4">
      <!-- Logo -->
      <div class="card p-sm-7 p-2">
        <!-- Reset Password -->
        <div class="app-brand justify-content-center mt-5">
          <a href="{{ url('/') }}" class="app-brand-link gap-3">
            <span class="app-brand-logo demo">@include('_partials.macros')</span>
            <span class="app-brand-text demo text-heading fw-semibold">{{ config('variables.templateName') }}</span>
          </a>
        </div>
        <!-- /Logo -->
        <div class="card-body mt-1">
          <h4 class="mb-1">Reset Password 🔐</h4>
          <p class="mb-5">Please enter your new password</p>

          @if ($errors->any())
          <div class="alert alert-danger" role="alert">
            @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
          </div>
          @endif

          <form id="formAuthentication" class="mb-5" action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-floating form-floating-outline mb-5">
              <input type="email" class="form-control @error('email') is-invalid @enderror" id="email-display" value="{{ $email }}" disabled />
              <label>Email</label>
            </div>

            <div class="form-floating form-floating-outline mb-5">
              <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter new password" autofocus required />
              <label>New Password</label>
              @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-floating form-floating-outline mb-5">
              <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" required />
              <label>Confirm Password</label>
            </div>

            <button type="submit" class="btn btn-primary d-grid w-100 mb-5">Reset Password</button>
          </form>

          <div class="text-center">
            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
              <i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-20px me-1_5"></i>
              Back to login
            </a>
          </div>
        </div>
      </div>
      <!-- /Reset Password -->
      <img src="{{ asset('assets/img/illustrations/tree-3.png') }}" alt="auth-tree" class="authentication-image-object-left d-none d-lg-block" />
      <img src="{{ asset('assets/img/illustrations/auth-basic-mask-light.png') }}" class="authentication-image d-none d-lg-block scaleX-n1-rtl" height="172" alt="triangle-bg" />
      <img src="{{ asset('assets/img/illustrations/tree.png') }}" alt="auth-tree" class="authentication-image-object-right d-none d-lg-block" />
    </div>
  </div>
</div>
@endsection