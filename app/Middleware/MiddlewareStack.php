<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MiddlewareStack
{
    protected array $middlewares;

    public function __construct(array $middlewares = [])
    {
        $this->middlewares = $middlewares;
    }

    public function add($middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $middleware = array_shift($this->middlewares);

        if ($middleware) {
            return $middleware($request, $response, function ($request, $response) use ($next) {
                return $this->__invoke($request, $response, $next);
            });
        }

        return $next($request, $response);
    }
}
