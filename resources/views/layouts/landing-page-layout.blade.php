<!DOCTYPE html>
<html lang="en" data-theme="corporate" class="bg-white">
<head>
    <meta charset="utf-8" />
    @include('layouts.title-meta')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Your destination for mystery scoops, BTS merch, and curated gift boxes." name="description" />
    <meta content="Ravellen" name="author" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('layouts.head-css')
</head>
<body class=" overflow-x-hidden font-poppins">

  {{-- <div class="drawer lg:drawer-open h-screen"> --}}
    {{-- ✅ Drawer Toggle for Mobile --}}
    {{-- <input id="drawer-toggle" type="checkbox" class="drawer-toggle" /> --}}

    <div class=" flex flex-col">
        {{-- ✅ Sticky Navbar --}}
        @include('layouts.navbar')
        @include('layouts.back-button')

      {{-- ✅ Main Content (scrollable) --}}
      <main class="overflow-y-hidden overflow-x-hidden">
        @yield('content')
        @yield('script')
      </main>


      {{-- ✅ Sticky Footer --}}
        @include('layouts.footer')

    </div>

    {{-- Bootstrap's JS drives the mobile menu (offcanvas) and the profile modals; pages must not load it again. --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @include('layouts.toastify')
    @include('layouts.live-updates')

    {{-- </div> --}}
  {{-- </div> --}}

</body>
</html>
