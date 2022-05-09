<?php

namespace Kitsune\Core\Middleware;

use Closure;
use Kitsune\Core\Concerns\UtilisesKitsune;

class KitsuneLayoutMiddleware
{
    use UtilisesKitsune;

    public function handle($request, Closure $next, $layout = null)
    {
        if ($layout) {
            $this->getKitsuneCore()->setApplicationLayout($layout);
        }

        return $next($request);
    }
}
