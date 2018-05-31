@extends('layouts.master')

@push('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endpush

@section('main')
    <div id="app"></div>
@stop

@push('scripts')
    <script>
        window.Laravel = {
            user: @json(auth()->user()->resource()),
        };
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
@endpush
