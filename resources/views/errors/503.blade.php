@extends('layouts/blankLayout')

@section('title', 'Maintenance')

@section('page-style')
<!-- Page -->
@vite(['resources/assets/vendor/scss/pages/page-misc.scss'])
@endsection

@section('content')
<!--Under Maintenance -->
<div class="misc-wrapper">
    <h4 class="mb-2 mx-2">Dalam Perbaikan! 🚧</h4>
    <p class="mb-10 mx-2">Mohon maaf atas ketidaknyamanan ini, tetapi kami sedang melakukan pemeliharaan saat ini.</p>
</div>
<!-- /Under Maintenance -->
@endsection
