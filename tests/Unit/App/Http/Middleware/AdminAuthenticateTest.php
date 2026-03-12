<?php

declare(strict_types=1);

namespace Tests\Unit\App\Http\Middleware;

use App\Http\Middleware\AdminAuthenticate;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AdminAuthenticateTest extends TestCase
{
    public function testRedirectToReturnsNullForJsonRequests(): void
    {
        $middleware = $this->buildMiddleware();
        $request = Request::create('/admin/home', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

        $this->assertNull($this->callRedirectTo($middleware, $request));
    }

    private function buildMiddleware(): AdminAuthenticate
    {
        // AdminAuthenticate extends Laravel's Authenticate which needs the auth
        // factory.  We bypass the constructor with reflection to focus on the
        // single method we care about.
        return (new \ReflectionClass(AdminAuthenticate::class))
            ->newInstanceWithoutConstructor();
    }

    private function callRedirectTo(AdminAuthenticate $middleware, Request $request): ?string
    {
        $method = new ReflectionMethod(AdminAuthenticate::class, 'redirectTo');

        return $method->invoke($middleware, $request);
    }
}
