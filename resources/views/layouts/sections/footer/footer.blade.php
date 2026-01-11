@php
$containerFooter = !empty($containerNav) ? $containerNav : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
    <div class="{{ $containerFooter }}">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="text-body">
                Copyright © 2026 R&B Dev. All Rights Reserved.
            </div>
            <!-- Right-side footer links removed per design request -->
        </div>
    </div>
</footer>
<!--/ Footer-->