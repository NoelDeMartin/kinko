<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @stack('meta')
        <title>@lang('kinko.title')</title>
        @stack('styles')
    </head>
    <body class="font-sans">
        @yield('main')
        <link crossorigin="anonymous" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu">
        @stack('scripts')
    </body>
</html>
