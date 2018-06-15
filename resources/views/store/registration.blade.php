@extends('layouts.store')

@section('main')
    <h1 class="mb-4">{{ trans('store.registration.title') }}</h1>
    <div class="form-box">
        <input type="text" value="{{ old('name', $name) }}">
        <application-details url="{{ $url }}"></application-details>
        <button type="submit">{{ trans('store.registration.submit') }}</button>
    </div>
@stop
