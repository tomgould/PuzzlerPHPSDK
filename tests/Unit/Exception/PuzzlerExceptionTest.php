<?php

namespace TomGould\PuzzlerPHPSDK\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;
use TomGould\PuzzlerPHPSDK\Exception\AuthenticationException;
use TomGould\PuzzlerPHPSDK\Exception\BadRequestException;
use TomGould\PuzzlerPHPSDK\Exception\NotFoundException;
use TomGould\PuzzlerPHPSDK\Exception\MethodNotAllowedException;
use TomGould\PuzzlerPHPSDK\Exception\ServerException;

/**
 * Unit tests for exceptions
 */
class PuzzlerExceptionTest extends TestCase
{
    public function testPuzzlerExceptionStoresResponseBody()
    {
        $responseBody = '{"error": "test error"}';
        $exception = new PuzzlerException('Test message', 500, $responseBody);

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertEquals($responseBody, $exception->getResponseBody());
    }

    public function testAuthenticationExceptionExtendsBase()
    {
        $exception = new AuthenticationException('Auth failed', 401);

        $this->assertInstanceOf(PuzzlerException::class, $exception);
        $this->assertEquals('Auth failed', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
    }

    public function testBadRequestExceptionExtendsBase()
    {
        $exception = new BadRequestException('Bad request', 400);

        $this->assertInstanceOf(PuzzlerException::class, $exception);
    }

    public function testNotFoundExceptionExtendsBase()
    {
        $exception = new NotFoundException('Not found', 404);

        $this->assertInstanceOf(PuzzlerException::class, $exception);
    }

    public function testMethodNotAllowedExceptionExtendsBase()
    {
        $exception = new MethodNotAllowedException('Method not allowed', 405);

        $this->assertInstanceOf(PuzzlerException::class, $exception);
    }

    public function testServerExceptionExtendsBase()
    {
        $exception = new ServerException('Server error', 500);

        $this->assertInstanceOf(PuzzlerException::class, $exception);
    }
}

