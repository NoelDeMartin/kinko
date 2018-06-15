<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Concerns\FakesGuzzleRequests;

class ApplicationTests extends TestCase
{
    use FakesGuzzleRequests;

    public function test_validate()
    {
        $url = $this->faker->url;
        $domain = $this->faker->domainName;
        $callbackUrl = '/' . $this->faker->word;
        $description = $this->faker->sentence();
        $schemaUrl = '/autonomous-data-schema.graphql';

        $this->withoutExceptionHandling();

        $this->fakeGuzzleRequests();
        $this->appendGuzzleResponse(200, [], [
            'domain'       => $domain,
            'callback_url' => $callbackUrl,
            'description'  => $description,
            'schema_url'   => $schemaUrl,
        ]);
        $this->appendGuzzleResponse(200, [], file_get_contents(stubs_path('schema.graphql')));

        $response = $this->login()->get('/api/applications/validate?' . http_build_query([
            'url' => $url,
        ]));

        // TODO assert that guzzle was called

        $response->assertSuccessful();
        $response->assertJsonStructure(['domain', 'callback_url', 'description', 'schema_url', 'schema']);

        $details = $response->json();
        $this->assertEquals($domain, $details['domain']);
        $this->assertEquals('https://' . $domain . $callbackUrl, $details['callback_url']);
        $this->assertEquals($description, $details['description']);
        $this->assertEquals('https://' . $domain . $schemaUrl, $details['schema_url']);
        $this->assertEquals(load_stub('schema.json'), $details['schema']);
    }

    // TODO test failing scenarios
}
