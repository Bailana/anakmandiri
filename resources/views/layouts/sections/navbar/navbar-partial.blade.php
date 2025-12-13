@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if(isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-6">
    <a href="{{url('/')}}" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">@include('_partials.macros')</span>
        <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
    </a>
</div>
@endif

<!-- ! Not required for layout-without-menu -->
@if(!isset($navbarHideToggle))
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
        <i class="icon-base ri ri-menu-line icon-md"></i>
    </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="icon-base ri ri-search-line icon-lg lh-0"></i>
            <input type="text" class="form-control border-0 shadow-none" placeholder="Search..." aria-label="Search...">
        </div>
    </div>
    <!-- /Search -->
    <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- Place this tag where you want the button to render. -->
        <li class="nav-item lh-1 me-4">
        </li>

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    @if(Auth::check() && Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle" />
                    @else
                    <img src="{{ asset('assets/img/avatars/1.svg') }}" alt="Default Avatar" class="rounded-circle" />
                    @endif
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    @if(Auth::check() && Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-px-40 h-auto rounded-circle" />
                                    @else
                                    <img src="{{ asset('assets/img/avatars/1.svg') }}" alt="Default Avatar" class="w-px-40 h-auto rounded-circle" />
                                    @endif
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ Auth::check() ? Auth::user()->name : 'John Doe' }}</h6>
                                <small class="text-body-secondary">{{ Auth::check() ? ucfirst(Auth::user()->role ?? 'User') : 'Admin' }}</small>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                        <i class="icon-base ri ri-user-3-line icon-md me-3"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <i class="icon-base ri ri-settings-4-line icon-md me-4"></i><span>Settings</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('auth-reset-password-basic') }}">
                        <i class="icon-base ri ri-lock-password-line icon-md me-3"></i>
                        <span>Reset Password</span>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <div class="d-grid px-4 pt-2 pb-1">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger d-flex w-100">
                                <small class="align-middle">Logout</small>
                                <i class="ri ri-logout-box-r-line ms-2 icon-xs"></i>
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>