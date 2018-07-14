<?php

namespace Kinko\Http\Controllers\Store\Web;

use Exception;
use Kinko\Models\Application;
use GuzzleHttp\ClientInterface;
use Kinko\Models\Passport\Client;
use Kinko\Support\Facades\GraphQL;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Kinko\Http\Controllers\Controller;
use Kinko\Http\Requests\StoreApplicationRequest;
use Kinko\Http\Requests\CreateApplicationRequest;
use Kinko\Exceptions\ApiError;
use Illuminate\Validation\ValidationException;

class ApplicationsController extends Controller
{
    public function create(CreateApplicationRequest $request)
    {
        return view('store.registration', $request->validated());
    }

    public function store(StoreApplicationRequest $request)
    {
        // TODO require permissions as well, see: https://www.graph.cool/docs/tutorials/auth/authorization-for-a-cms-miesho4goo

        $client = Client::create([
            'user_id' => Auth::id(),
            'name' => $request->input('name'),
            'secret' => str_random(40),
            'redirect' => $request->input('redirect_url'),
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
        ]);

        $application = Application::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'domain' => $request->input('domain'),
            'redirect_url' => $request->input('redirect_url'),
            'callback_url' => $request->input('callback_url'),
            'schema' => GraphQL::parseJsonSchema($request->input('schema')),
            'client_id' => $client->id,
        ]);

        $state = $request->input('state');
        $this->sendApplicationDetails($application, $client, $state);

        return redirect($application->redirect_url . '?' . http_build_query(compact('state')));
    }

    private function sendApplicationDetails($application, $client, $state)
    {
        $guzzleClient = App::make(ClientInterface::class);

        try {
            // TODO send application schema information

            $response = $guzzleClient->post($application->callback_url, [
                'form_params' => [
                    'state' => $state,
                    'client_id' => $client->id,
                    'client_secret' => $client->secret,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new Exception;
            }
        } catch (Exception $e) {
            $application->delete();
            $client->delete();

            throw ValidationException::withMessages([
                'Could not get a confirmation response from ' . $application->callback_url
            ]);
        }
    }
}
