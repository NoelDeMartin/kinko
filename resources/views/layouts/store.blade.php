@extends('layouts.master')

@push('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
@endpush

@section('main')
    <div id="app" class="w-screen h-screen flex flex-col items-center justify-center bg-grey-lighter">
        @yield('main')
    </div>
@overwrite

@push('scripts')
    <script>
        window.Laravel = {
            baseUrl: @json(url('')),
            lang: @json(['store' => trans('store')]),
        };
    </script>
    <script src="{{ asset('js/store.js') }}"></script>
@endpush
