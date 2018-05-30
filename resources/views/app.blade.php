@extends('layouts.master')

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
