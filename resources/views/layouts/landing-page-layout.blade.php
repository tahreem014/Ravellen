<!DOCTYPE html>
<html lang="en" data-theme="corporate" class="bg-white">
<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Velzon - Admin & Dashboard Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico')}}">
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

      {{-- ✅ Main Content (scrollable) --}}
      <main class="overflow-y-hidden overflow-x-hidden">
        @yield('content')
        @yield('script')
      </main>


      {{-- ✅ Sticky Footer --}}
        @include('layouts.footer')

    </div>

    @include('layouts.toastify')
    @include('layouts.live-updates')

    {{-- </div> --}}
  {{-- </div> --}}

</body>
</html>
