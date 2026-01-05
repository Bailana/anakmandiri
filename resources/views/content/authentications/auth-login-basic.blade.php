@extends('layouts/blankLayout')

@section('title', 'Login | Anak Mandiri')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
@if (session('status'))
<script>
  // Toast helper jika belum ada
  if (typeof window.showToast !== 'function') {
    window.showToast = function (message, type = 'success') {
      let toast = document.getElementById('customToast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'customToast';
        toast.className = 'toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-4';
        toast.style.zIndex = 9999;
        toast.innerHTML =
          '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        document.body.appendChild(toast);
      } else {
        toast.className = 'toast align-items-center text-bg-' + type + ' border-0 position-fixed bottom-0 end-0 m-4';
      }
      toast.querySelector('.toast-body').textContent = message;
      var bsToast = window.bootstrap && typeof window.bootstrap.Toast === 'function' ? window.bootstrap.Toast
        .getOrCreateInstance(toast, {
          delay: 2000
        }) : null;
      if (bsToast) bsToast.show();
      else {
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2000);
      }
    }
  }
  window.addEventListener('DOMContentLoaded', function () {
    window.showToast(@json(session('status')), 'success');
  });

</script>
@endif
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6 mx-4">
      <!-- Login -->
      <div class="card p-sm-7 p-2">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-5">
          <a href="{{url('/')}}" class="app-brand-link">
            <span class="app-brand-logo demo me-1">
              <img src="{{ asset('assets/img/am.png') }}" alt="Logo" style="height:60px;">
            </span>
          </a>
        </div>
        <!-- /Logo -->

        <div class="card-body mt-1">
          <h4 class="mb-1 text-center">Klinik Terapis & Sekolah Khusus Anak Mandiri</h4>
          <p class="mb-5">Silakan masuk ke akun Anda untuk melanjutkan</p>


          @if (session('status'))
          <script>
            window.addEventListener('DOMContentLoaded', function () {
              window.showToast && window.showToast(@json(session('status')), 'success');
            });

          </script>
          @endif

          @if ($errors->any())
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Login Gagal!</strong>
            @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          <form id="formAuthentication" class="mb-5" action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-floating form-floating-outline mb-5 form-control-validation">
              <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                placeholder="Masukkan email Anda" value="{{ old('email') }}" autofocus />
              <label for="email">Email</label>
              @error('email')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="mb-5">
              <div class="form-password-toggle form-control-validation">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="password" id="password" class="form-control @error('password') is-invalid @enderror"
                      name="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password" />
                    <label for="password">Password</label>
                    @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>
                  <span class="input-group-text cursor-pointer"><i
                      class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                </div>
              </div>
            </div>
            <div class="mb-5 pb-2 d-flex justify-content-between pt-2 align-items-center">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                <label class="form-check-label" for="remember-me"> Ingat Saya </label>
              </div>
              <a href="{{ url('auth/forgot-password-basic') }}" class="float-end mb-1">
                <span>Lupa Password?</span>
              </a>
            </div>
            <div class="mb-5">
              <button class="btn btn-primary d-grid w-100" type="submit">Masuk</button>
            </div>
          </form>
          <!-- <p class="text-center mb-5">
                        <span>New on our platform?</span>
                        <a href="{{ url('auth/register-basic') }}">
                            <span>Create an account</span>
                        </a>
                    </p> -->
        </div>
      </div>
    </div>
  </div>
  @endsection

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var emailEl = document.getElementById('email');
      var rememberEl = document.getElementById('remember-me');
      var form = document.getElementById('formAuthentication');

      try {
        var storedEmail = localStorage.getItem('am_remember_email');
        if (storedEmail) {
          emailEl.value = storedEmail;
          rememberEl.checked = true;
        }
      } catch (e) {
        console.warn('Could not read remembered email', e);
      }

      if (form) {
        form.addEventListener('submit', function () {
          try {
            if (rememberEl && rememberEl.checked && emailEl.value) {
              localStorage.setItem('am_remember_email', emailEl.value);
            } else {
              localStorage.removeItem('am_remember_email');
            }
          } catch (e) {
            console.warn('Could not save remembered email', e);
          }
        });
      }
    });

  </script>
