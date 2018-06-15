<?php

namespace Kinko\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Str;
use Kinko\Exceptions\ApiError;
use GuzzleHttp\ClientInterface;
use Kinko\Support\Facades\GraphQL;
use Kinko\Http\Controllers\Controller;
use Kinko\Http\Requests\ValidateApplicationRequest;

class ApplicationValidationsController extends Controller
{
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function __invoke(ValidateApplicationRequest $request)
    {
        $details = $this->retrieveApplicationDetails($request->input('url'));

        $details['schema'] = $this->retrieveApplicationSchema($details);

        return $details;
    }

    private function retrieveApplicationDetails($url)
    {
        $url .= '/autonomous-data.json';

        try {
            $response = $this->client->get($url);

            if ($response->getStatusCode() !== 200) {
                throw new ApiError('Could not get application details from ' . $url);
            }

            $details = json_decode($response->getBody()->getContents());

            if ((
                !property_exists($details, 'domain') ||
                !property_exists($details, 'callback_url') ||
                !property_exists($details, 'description') ||
                !property_exists($details, 'schema_url')
            )) {
                throw new ApiError('Application details format is invalid');
            }

            if (property_exists($details, 'protocol') && !in_array($details->protocol, ['http', 'https'])) {
                throw new ApiError('Application protocol must be http or https');
            }

            // TODO perform a more thorough check to see if the callback_url is within the domain
            if (Str::startsWith($details->callback_url, 'http') && !Str::contains($details->callback_url, $details->domain)) {
                throw new ApiError('Application callback_url must be within the application domain');
            }

            // TODO use validator to validate details (and use $validator->validated() to get details)

            $protocol = isset($details->protocol) ? $details->protocol : 'https';

            if (!Str::startsWith($details->callback_url, 'http')) {
                $details->callback_url = $protocol . '://' . $details->domain . $details->callback_url;
            }

            if (!Str::startsWith($details->schema_url, 'http')) {
                $details->schema_url = $protocol . '://' . $details->domain . $details->schema_url;
            }

            return (array) $details;
        } catch (Exception $e) {
            throw new ApiError('Could not get application details from ' . $url);
        }
    }

    private function retrieveApplicationSchema($details)
    {
        $url = $details['schema_url'];

        try {
            $response = $this->client->get($url);

            if ($response->getStatusCode() !== 200) {
                throw new ApiError('Could not get application schema from ' . $url);
            }

            $schema = GraphQL::parseSchema($response->getBody()->getContents(), true);

            return GraphQL::serializeSchema($schema);
        } catch (Exception $e) {
            throw new ApiError('Could not get application schema from ' . $url);
        }
    }
}
