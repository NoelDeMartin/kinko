<?php

namespace Tests\Concerns;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\Assert as PHPUnit;

trait FakesGuzzleRequests
{
    protected $mockHandler;

    protected $history;

    protected function fakeGuzzleRequests()
    {
        $this->history = [];
        $this->mockHandler = new MockHandler();

        $handler = HandlerStack::create($this->mockHandler);
        $handler->push(Middleware::history($this->history));
        $this->app->instance(ClientInterface::class, new Client(['handler' => $handler]));

        return $this;
    }

    protected function appendGuzzleResponse($status = 200, array $headers = [], $body = null)
    {
        if (!is_null($body) && !is_string($body)) {
            $body = json_encode($body);
        }

        $this->mockHandler->append(new Response($status, $headers, $body));
    }

    protected function assertGuzzleCalled($url, $method = 'GET', $headers = [], $body = null)
    {
        $this->assertTrue(
            $this->guzzleCalled($url, $method, $headers, $body),
            'Guzzle request with url [' . $url . '] was not called'
        );
    }

    protected function assertGuzzleCalledTimes($times)
    {
        $this->assertTrue(
            count($this->history) === $times,
            'Guzzle was called ' . count($this->history) . ' times, expected ' . $times . ' times'
        );
    }

    protected function assertGuzzleNotCalled()
    {
        $this->assertTrue(count($this->history) === 0, 'Guzzle was called ' . count($this->history) . ' times.');
    }

    protected function guzzleCalled($url, $method = 'GET', $headers = [], $body = null)
    {
        foreach ($this->history as $transaction) {
            if ($this->guzzleRequestMatches($transaction['request'], $url, $method, $headers, $body)) {
                return true;
            }
        }

        return false;
    }

    private function guzzleRequestMatches(Request $request, $url, $method, $headers, $body)
    {
        return strval($request->getUri()) === $url
            && $request->getMethod() === $method
            && $this->guzzleRequestContainsHeaders($request, $headers)
            && is_null($body) || $this->guzzleRequestBodyMatches($request, $body);
    }

    private function guzzleRequestContainsHeaders(Request $request, $headers)
    {
        foreach ($headers as $header => $value) {
            if ($request->getHeaderLine($header) !== $value) {
                return false;
            }
        }

        return true;
    }

    private function guzzleRequestBodyMatches(Request $request, $body)
    {
        // TODO refactor this, and encode depending on content type
        $bodyParts = [];
        foreach ($body as $key => $value) {
            $bodyParts[] = urlencode($key) . '=' . urlencode($value);
        }
        $body = implode('&', $bodyParts);

        return $request->getBody()->getContents() === $body;
    }
}
