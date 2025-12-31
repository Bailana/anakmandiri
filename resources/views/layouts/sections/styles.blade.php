<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap" rel="stylesheet" />

<!-- Fonts Icons -->
@vite(['resources/assets/vendor/fonts/iconify/iconify.css'])

<!-- BEGIN: Vendor CSS-->
@vite(['resources/assets/vendor/libs/node-waves/node-waves.scss'])


<!-- Core CSS -->
@vite(['resources/assets/vendor/scss/core.scss', 'resources/assets/css/demo.css'])

<!-- Vendor Styles -->
@vite('resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss')
@yield('vendor-style')

<!-- Page Styles -->
@yield('page-style')

<!-- app CSS -->
@vite(['resources/css/app.css'])
<!-- END: app CSS-->

<!-- Force avatar images to stay circular on all screen sizes -->
<style>
  /* ensure any img with rounded-circle remains perfectly round */
  img.rounded-circle,
  .avatar img,
  .avatar-initial {
    border-radius: 50% !important;
    overflow: hidden !important;
  }

  /* make sure avatar images cover their box without distortion */
  .avatar img,
  img.rounded-circle {
    object-fit: cover;
    display: inline-block;
  }

  /* keep avatars visually circular when width/height change on small screens */
  .avatar,
  .avatar.avatar-xl,
  .avatar.avatar-sm,
  .avatar-initial {
    border-radius: 50% !important;
  }
</style>