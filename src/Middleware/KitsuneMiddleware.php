<?php

namespace Shiriso\Kitsune\Core\Middleware;

use Closure;
use Shiriso\Kitsune\Core\Concerns\UtilisesKitsune;

class KitsuneMiddleware
{
    use UtilisesKitsune;

    public function handle($request, Closure $next, $layout = null)
    {
        if ($layout) {
            $this->getKitsuneManager()->setApplicationLayout($layout);
        }

        return $next($request);
    }
}