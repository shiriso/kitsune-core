<?php

namespace Shiriso\Kitsune\Core\Concerns;

use Shiriso\Kitsune\Core\Contracts\ProvidesKitsuneCore;
use Shiriso\Kitsune\Core\Contracts\ProvidesKitsuneHelper;
use Shiriso\Kitsune\Core\Contracts\ProvidesKitsuneManager;

/**
 * This trait only offers basic functionalities to request Kitsune's services in a way
 * that IDEs will usually be able to provide auto-completion when using them.
 *
 * As calls to app() are not type hinted to a specific type, they would otherwise
 * not be able to understand what kind of data gets returned.
 *
 * Type hinting the interface allows at least some code completion and information
 * about the minimum implementation of the returned object while maintaining
 * the option to define a diverging class in the core configuration.
 */
trait UtilisesKitsune
{
    /**
     * Returns Kitsune core from the container.
     *
     * @return ProvidesKitsuneCore
     */
    public function getKitsuneCore(): ProvidesKitsuneCore
    {
        return app('kitsune');
    }

    /**
     * Returns Kitsune helper from the container.
     *
     * @return ProvidesKitsuneHelper
     */
    public function getKitsuneHelper(): ProvidesKitsuneHelper
    {
        return app('kitsune.helper');
    }

    /**
     * Returns Kitsune manager from the container.
     *
     * @return ProvidesKitsuneManager
     */
    public function getKitsuneManager(): ProvidesKitsuneManager
    {
        return app('kitsune.manager');
    }
}