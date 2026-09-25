<?php

/**
 * IDE helper stubs for static analysis (Intelephense / PHPStorm).
 *
 * @see https://github.com/barryvdh/laravel-ide-helper
 */

namespace Illuminate\Support\Facades {

    /**
     * @method static \Illuminate\Routing\RouteRegistrar prefix(string $prefix)
     * @method static \Illuminate\Routing\RouteRegistrar name(string $name)
     * @method static \Illuminate\Routing\Route get(string $uri, array|string|callable|null $action = null)
     * @method static \Illuminate\Routing\Route post(string $uri, array|string|callable|null $action = null)
     * @method static void group(\Closure|array $attributes, \Closure|string|array|null $routes = null)
     *
     * @see \Illuminate\Routing\Router
     */
    class Route extends \Illuminate\Support\Facades\Facade {}
}

namespace {
    /**
     * @param  array<string, mixed>|null  $headers
     */
    function response(mixed $content = '', int $status = 200, array $headers = []): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        return new \Illuminate\Http\Response($content, $status, $headers);
    }

    /**
     * @param  array<string, mixed>|null  $default
     * @return mixed
     */
    function config(?string $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}
