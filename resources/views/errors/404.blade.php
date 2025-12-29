@extends('layouts/blankLayout')

@section('title', 'Halaman Tidak Ditemukan')

@section('page-style')
<!-- Page -->
@vite(['resources/assets/vendor/scss/pages/page-misc.scss'])
@endsection

@section('content')
<!-- Error -->
<div class="misc-wrapper">
  <h1 class="mb-2 mx-2" style="font-size: 6rem;line-height: 6rem;">404</h1>
  <h4 class="mb-2">Halaman Tidak Ditemukan 🙄</h4>
  <p class="mb-10 mx-2">Kami tidak dapat menemukan halaman yang Anda cari.</p>
</div>
<!-- /Error -->
@endsection