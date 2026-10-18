<?php

namespace TomGould\PuzzlerPHPSDK\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use TomGould\PuzzlerPHPSDK\Exception\AuthenticationException;
use TomGould\PuzzlerPHPSDK\Exception\BadRequestException;
use TomGould\PuzzlerPHPSDK\Exception\MethodNotAllowedException;
use TomGould\PuzzlerPHPSDK\Exception\NotFoundException;
use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;
use TomGould\PuzzlerPHPSDK\Exception\ServerException;
use TomGould\PuzzlerPHPSDK\Http\HttpClient;

/**
 * Offline tests for request building, signing and error mapping.
 */
class HttpClientMockedTest extends TestCase
{
    private function fake(array $reply)
    {
        return new class('cid', 'key', 'secret', 'https://api.example.com/', $reply) extends HttpClient {
            public $calls = [];
            private $reply;

            public function __construct($c, $k, $s, $u, array $reply)
            {
                parent::__construct($c, $k, $s, $u);
                $this->reply = $reply;
            }

            protected function send($method, $url, array $headers, $jsonBody)
            {
                $this->calls[] = compact('method', 'url', 'headers', 'jsonBody');
                return $this->reply;
            }
        };
    }

    public function testRequestIsSignedAndUrlBuilt()
    {
        $client = $this->fake(['{"ok":true}', 200, 'application/json; charset=utf-8', '']);
        $before = time();
        $result = $client->post('/api/Puzzle/collect', ['a' => 1]);
        $after = time();

        $this->assertSame(['ok' => true], $result);
        $call = $client->calls[0];
        $this->assertSame('POST', $call['method']);
        $this->assertSame('https://api.example.com/api/Puzzle/collect', $call['url']);
        $this->assertSame('{"a":1}', $call['jsonBody']);

        $headers = [];
        foreach ($call['headers'] as $line) {
            list($name, $value) = explode(': ', $line, 2);
            $headers[$name] = $value;
        }
        $this->assertSame('cid', $headers['X-Client-Id']);
        $this->assertSame('key', $headers['X-Api-Key']);
        $this->assertGreaterThanOrEqual($before, (int)$headers['X-Timestamp']);
        $this->assertLessThanOrEqual($after, (int)$headers['X-Timestamp']);

        $expected = base64_encode(hash('sha256', 'key_cid_' . $headers['X-Timestamp'] . '_secret', true));
        $this->assertSame($expected, $headers['X-Signature']);
    }

    public function testGetSendsNoBody()
    {
        $client = $this->fake(['hello', 200, 'text/plain', '']);
        $this->assertSame('hello', $client->get('/api/Health/check'));
        $this->assertNull($client->calls[0]['jsonBody']);
    }

    public function testInvalidJsonThrows()
    {
        $client = $this->fake(['{nope', 200, 'application/json', '']);
        $this->expectException(PuzzlerException::class);
        $client->get('/x');
    }

    public function testCurlErrorThrows()
    {
        $client = $this->fake([false, 0, '', 'Could not resolve host']);
        $this->expectException(PuzzlerException::class);
        $this->expectExceptionMessage('cURL error: Could not resolve host');
        $client->get('/x');
    }

    /**
     * @dataProvider errorProvider
     */
    public function testStatusCodesMapToExceptions($status, $class)
    {
        $client = $this->fake(['body-text', $status, 'text/plain', '']);
        try {
            $client->get('/x');
            $this->fail('Expected exception');
        } catch (PuzzlerException $e) {
            $this->assertInstanceOf($class, $e);
            $this->assertSame($status, $e->getCode());
            $this->assertSame('body-text', $e->getResponseBody());
        }
    }

    public function errorProvider()
    {
        return [
            [400, BadRequestException::class],
            [401, AuthenticationException::class],
            [404, NotFoundException::class],
            [405, MethodNotAllowedException::class],
            [500, ServerException::class],
            [502, ServerException::class],
            [418, PuzzlerException::class],
        ];
    }
}
