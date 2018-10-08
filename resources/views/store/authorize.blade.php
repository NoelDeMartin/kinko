@extends('layouts.web')

@section('main')
    <h1 class="mb-4">@lang('kinko.authorization.title')</h1>
    <div class="form-box">
        <p class="mb-2">
            <a href="https://www.oauth.com/oauth2-servers/authorization/the-authorization-interface/">
                TODO: review oauth guidelines and see source passport views!
            </a>
        </p>

        <client-details :client-json="{{ json_encode($client->resource()->resolve()) }}"></client-details>

        <form method="POST" action="{{ route('store.authorize.approve') }}">
            @csrf

            <input type="hidden" name="state" value="{{ $state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <button type="submit">@lang('kinko.authorization.approve')</button>
        </form>

        <form class="mt-2" method="POST" action="{{ route('store.authorize.deny') }}">
            @csrf
            @method('DELETE')

            <input type="hidden" name="state" value="{{ $state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <button type="submit" class="secondary">@lang('kinko.authorization.deny')</button>
        </form>
    </div>
@stop
