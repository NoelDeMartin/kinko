<?php

namespace Kinko\Http\Controllers\Api;

use Exception;
use Kinko\Exceptions\ApiError;
use GuzzleHttp\ClientInterface;
use Kinko\Support\Facades\GraphQL;
use Kinko\Http\Controllers\Controller;
use Kinko\Http\Requests\ParseApplicationSchemaRequest;

class ApplicationSchemasParsingController extends Controller
{
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function __invoke(ParseApplicationSchemaRequest $request)
    {
        $url = $request->input('url');

        try {
            $response = $this->client->get($url);

            if ($response->getStatusCode() !== 200) {
                throw new Exception;
            }

            $schema = GraphQL::parseGraphQLSchema($response->getBody()->getContents(), true);

            // TODO return specific validation errors if necessary

            return $schema->toArray();
        } catch (Exception $e) {
            throw new ApiError('Could not get application schema from ' . $url);
        }
    }
}
