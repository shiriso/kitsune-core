<?php

namespace Shiriso\Kitsune\Core\Middleware;

use Closure;

class KitsuneGlobalModeMiddleware
{
    public function handle($request, Closure $next, $state = true)
    {
        if(!$state || $state === 'false') {
            app('kitsune')->disableGlobalMode();
        } else {
            app('kitsune')->enableGlobalMode();
        }

        return $next($request);
    }
}