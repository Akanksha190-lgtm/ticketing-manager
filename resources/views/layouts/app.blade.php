<!DOCTYPE html>
<html>
<head>
    <title>Mann Travel — Ticketing Desk</title>    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('styles')
</head>

<body>
    <div class="app-shell">
        {{-- Sidebar --}}
        @include('layouts.navbar')
        <div class="main-col">
            @yield('content')
        </div>
    </div>
@stack('scripts')
</body>
</html>