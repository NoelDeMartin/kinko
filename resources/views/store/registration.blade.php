@extends('layouts.store')

@section('main')
    <h1 class="mb-4">{{ trans('store.registration.title') }}</h1>

    <form class="form-box" method="POST" action="{{ route('store.register') }}">
        @csrf

        <input type="text" name="name" value="{{ old('name', $name) }}">

        @if ($errors->any())
            <div class="text-error mt-2">
                @foreach ($errors->all() as $error)
                    <p class="mb-2">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <application-details
            description="{{ $description }}"
            domain="{{ $domain }}"
            callback-url="{{ $callback_url }}"
            redirect-url="{{ $redirect_url }}"
            schema-url="{{ $schema_url }}"
        >
            <template slot-scope="application">
                <input name="schema" :value="JSON.stringify(application.schema)" type="hidden">
            </template>
        </application-details>

        <input name="description" value="{{ $description }}" type="hidden">
        <input name="domain" value="{{ $domain }}" type="hidden">
        <input name="callback_url" value="{{ $callback_url }}" type="hidden">
        <input name="redirect_url" value="{{ $redirect_url }}" type="hidden">
        <input name="state" value="{{ $state }}" type="hidden">

        <button type="submit">{{ trans('store.registration.submit') }}</button>
    </form>
@stop
