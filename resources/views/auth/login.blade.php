@extends('layouts.web')

@section('main')
    <h1 class="flex justify-center align-center mb-2">
        <img src="https://png.icons8.com/color/40/000000/safe.png">
        @lang('kinko.title')
    </h1>
    <form method="POST" action="{{ route('login') }}" class="form-box w-96">
        @csrf

        <input type="email" name="email" value="{{ old('email') }}" required autofocus>

        <input type="password" name="password" required>

        @foreach($errors->all() as $message)
            <p class="error">{{ $message }}</p>
        @endforeach

        <button type="submit">
            @lang('kinko.login.submit')
        </button>
    </form>
@stop
