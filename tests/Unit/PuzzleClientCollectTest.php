<?php

namespace TomGould\PuzzlerPHPSDK\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TomGould\PuzzlerPHPSDK\Client\PuzzleClient;
use TomGould\PuzzlerPHPSDK\Http\HttpClient;

/**
 * The exact JSON body collect() sends, checked offline.
 */
class PuzzleClientCollectTest extends TestCase
{
    private function fake()
    {
        return new class('cid', 'key', 'secret', 'https://api.example.com/') extends HttpClient {
            public $bodies = [];

            protected function send($method, $url, array $headers, $jsonBody)
            {
                $this->bodies[] = $jsonBody;
                return ['[]', 200, 'application/json', ''];
            }
        };
    }

    public function testNoFiltersSendsAnEmptyModelObject()
    {
        $http = $this->fake();
        (new PuzzleClient($http))->collect();
        $this->assertSame('{"model":{}}', $http->bodies[0]);
    }

    public function testFiltersAreSentUnwrapped()
    {
        $http = $this->fake();
        (new PuzzleClient($http))->collect([
            'puzzleDate' => '2026-11-22',
            'puzzleTypes' => ['SUD'],
        ]);
        $this->assertSame('{"puzzleDate":"2026-11-22","puzzleTypes":["SUD"]}', $http->bodies[0]);
    }
}
