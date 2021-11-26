<?php

namespace Shiriso\Kitsune\Core\Middleware;

use Closure;

class KitsuneMiddleware
{
    public function handle($request, Closure $next, $layout = null)
    {
        if($layout) {
            app('kitsune')->setActiveLayout($layout);
        } else {
            app('kitsune')->refreshViewSources();
        }

        return $next($request);
    }
}