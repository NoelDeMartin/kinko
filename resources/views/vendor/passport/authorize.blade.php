@extends('layouts.store')

@section('main')
    <h1 class="mb-4">{{ trans('store.authorization.title') }}</h1>
    <div class="form-box">
        <p class="mb-2">
            <a href="https://www.oauth.com/oauth2-servers/authorization/the-authorization-interface/">
                TODO: review oauth guidelines and see source passport views!
            </a>
        </p>

        <form method="POST" action="{{ route('store.authorize.approve') }}">
            @csrf

            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <button type="submit">{{ trans('store.authorization.approve') }}</button>
        </form>

        <form class="mt-2" method="POST" action="{{ route('store.authorize.deny') }}">
            @csrf
            @method('DELETE')

            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <button type="submit" class="secondary">{{ trans('store.authorization.deny') }}</button>
        </form>
    </div>
@stop
