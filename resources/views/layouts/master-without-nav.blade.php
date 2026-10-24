<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="light" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

    <head>
    <meta charset="utf-8" />
    @include('layouts.title-meta')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Your destination for mystery scoops, BTS merch, and curated gift boxes." name="description" />
    <meta content="Ravellen" name="author" />
        @include('layouts.head-css')
  </head>

    @yield('body')

    @yield('content')

    @include('layouts.vendor-scripts')
    @include('layouts.toastify')
    </body>
</html>
