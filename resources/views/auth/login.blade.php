@extends('layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('main')

    <form method="POST" action="{{ route('login') }}">

        @csrf

        <h1><img src="https://png.icons8.com/color/40/000000/safe.png"> 金庫</h1>

        <input type="email" name="email" value="{{ old('email') }}" required autofocus>

        <input type="password" name="password" required>

        @foreach($errors->all() as $message)
            <p class="error">{{ $message }}</p>
        @endforeach

        <button type="submit">
            Login
        </button>

    </form>

@stop
