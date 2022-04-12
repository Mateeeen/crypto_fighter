<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Metal Estructuras</title>
{{--CSS--}}
    @include('layout.partials.styles')
</head>
<body class="sidebar-dark">
<div class="main-wrapper">
    <!-- SIDEBAR -->
    @include('layout.partials.sidebar')
    <!-- SIDEBAR -->
    <div class="page-wrapper">
        <!-- NAV BAR -->
        @include('layout.partials.topnav')
        <!-- NAV BAR -->
        <div class="page-content">
            @yield('content')
        </div>
        <!-- FOOTER -->
        @include('layout.partials.footer')
        <!-- FOOTER -->
    </div>
</div>
{{--SCRIPTS--}}
@include('layout.partials.scripts')
</body>
</html>
