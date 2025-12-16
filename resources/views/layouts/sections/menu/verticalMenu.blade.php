@php
use Illuminate\Support\Facades\Route;
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu">

    <!-- ! Hide app brand if navbar-full -->
    <div class="app-brand demo">
        <a href="{{url('/')}}" class="app-brand-link">
            <span class="app-brand-logo demo me-1">
                <img src="{{ asset('assets/img/am.png') }}" alt="Logo" style="height:40px;">
            </span>
            <span class="app-brand-text demo menu-text fw-semibold ms-2">Anak Mandiri</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="menu-toggle-icon d-xl-inline-block align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)
        @php
        $showMenu = true;
        // Sembunyikan menu berdasarkan properti roles jika ada
        if (isset($menu->roles) && is_array($menu->roles)) {
        if (!auth()->check() || !in_array(auth()->user()->role, $menu->roles)) {
        $showMenu = false;
        }
        }
        // Sembunyikan menu karyawan & konsultan pada sidebar jika bukan admin (legacy, untuk menu lama)
        if (
        (isset($menu->slug) && $menu->slug === 'karyawan.index' && (!auth()->check() || auth()->user()->role !== 'admin'))
        ||
        (isset($menu->slug) && $menu->slug === 'konsultan.index' && (!auth()->check() || auth()->user()->role !== 'admin'))
        ) {
        $showMenu = false;
        }
        // Tampilkan menu program untuk admin dan konsultan
        if (isset($menu->slug) && $menu->slug === 'program.index' && (!auth()->check() || !in_array(auth()->user()->role,
        ['admin', 'konsultan']))) {
        $showMenu = false;
        }
        // Tampilkan menu assessment (penilaian anak) untuk admin dan guru saja
        if (isset($menu->slug) && $menu->slug === 'assessment.index' && (!auth()->check() || !in_array(auth()->user()->role,
        ['admin', 'guru']))) {
        $showMenu = false;
        }
        @endphp
        @if($showMenu)

        {{-- adding active and open class if child is active --}}

        {{-- menu headers --}}
        @if (isset($menu->menuHeader))
        <li class="menu-header mt-7">
            <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
        </li>
        @else

        {{-- active menu method --}}
        @php
        $activeClass = null;
        $currentRouteName = Route::currentRouteName();
        $currentUrl = request()->path();
        // Cek slug menu utama
        $mainSlugs = ['karyawan', 'anak-didik', 'konsultan', 'program', 'assessment'];
        if (in_array($menu->slug, $mainSlugs)) {
        if (request()->is($menu->slug) || request()->is($menu->slug.'/*')) {
        $activeClass = 'active open';
        }
        } else if ($currentRouteName === $menu->slug) {
        $activeClass = 'active';
        } elseif (isset($menu->submenu)) {
        if (gettype($menu->slug) === 'array') {
        foreach($menu->slug as $slug){
        if (str_contains($currentRouteName,$slug) and strpos($currentRouteName,$slug) === 0) {
        $activeClass = 'active open';
        }
        }
        } else {
        if (str_contains($currentRouteName,$menu->slug) and strpos($currentRouteName,$menu->slug) === 0) {
        $activeClass = 'active open';
        }
        }
        }
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{$activeClass}}">
            @if(isset($menu->slug) && $menu->slug === 'anak-didik.index' && auth()->user() && auth()->user()->role ===
            'admin')
            <a href="{{ route('anak-didik.index') }}" class="menu-link">
                @else
                <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                    class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and
                    !empty($menu->target)) target="_blank" @endif>
                    @endif
                    @isset($menu->icon)
                    <i class="{{ $menu->icon }}"></i>
                    @endisset
                    <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
                    @isset($menu->badge)
                    <div class="badge rounded-pill bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
                    @endisset
                </a>

                {{-- submenu --}}
                @isset($menu->submenu)
                @include('layouts.sections.menu.submenu',['menu' => $menu->submenu])
                @endisset
        </li>
        @endif
        @endif
        @endforeach
    </ul>

</aside>