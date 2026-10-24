{{--
    Tab title and icon, included by every layout. The title reads
    "Page | Ravellen" from the page's @section('title'), or just "Ravellen"
    when it sets none; the icon is the Ravellen logo (public/favicon.ico and
    apple-touch-icon.png, made from images/ravellen-logo.png).
--}}
<title>@hasSection('title')@yield('title') |@endif Ravellen</title>
<link rel="icon" href="{{ asset('favicon.ico') }}?v=2">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
