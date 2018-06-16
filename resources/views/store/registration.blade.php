@extends('layouts.store')

@section('main')
    <h1 class="mb-4">{{ trans('store.registration.title') }}</h1>

    <form class="form-box" method="POST">
        @csrf

        <input type="text" value="{{ old('name', $name) }}">

        @if ($errors->any())
            <div class="text-error mt-2">
                @foreach ($errors->all() as $error)
                    <p class="mb-2">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <application-details url="{{ $url }}">
            <template slot-scope="application">
                <input :value="application.description" name="description" type="hidden">
                <input :value="application.domain" name="domain" type="hidden">
                <input :value="application.callbackUrl" name="callback_url" type="hidden">
                <input :value="JSON.stringify(application.schema)" name="schema" type="hidden">
            </template>

        </application-details>

        <button type="submit">{{ trans('store.registration.submit') }}</button>
    </form>
@stop
