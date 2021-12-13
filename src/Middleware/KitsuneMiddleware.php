<?php

namespace Shiriso\Kitsune\Core\Middleware;

use Closure;
use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;

class KitsuneMiddleware
{
    use UtilisesKitsune;

    public function handle($request, Closure $next)
    {
        $this->getKitsuneCore()->initialize();

        return $next($request);
    }
}
