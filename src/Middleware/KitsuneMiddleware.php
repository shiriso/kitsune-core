<?php

namespace Kitsune\Core\Middleware;

use Closure;
use Kitsune\Core\Concerns\UtilisesKitsune;

class KitsuneMiddleware
{
    use UtilisesKitsune;

    public function handle($request, Closure $next)
    {
        $this->getKitsuneCore()->initialize();

        return $next($request);
    }
}
