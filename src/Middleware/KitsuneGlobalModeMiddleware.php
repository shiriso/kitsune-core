<?php

namespace Kitsune\Core\Middleware;

use Closure;
use Kitsune\Core\Concerns\UtilisesKitsune;

class KitsuneGlobalModeMiddleware
{
    use UtilisesKitsune;

    public function handle($request, Closure $next, $state = true)
    {
        if (!$state || $state === 'false') {
            $this->getKitsuneCore()->disableGlobalMode();
        } else {
            $this->getKitsuneCore()->enableGlobalMode();

            if (is_string($state) && $this->getKitsuneManager()->hasNamespace($state)) {
                $this->getKitsuneCore()->setGlobalNamespace($state);
            }
        }

        return $next($request);
    }
}
