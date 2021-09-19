<?php

declare(strict_types=1);

namespace Sage\Error;

/**
 * This interface is used for [default error formatting](error-handling.md).
 *
 * Only errors implementing this interface (and returning true from `isClientSafe()`)
 * will be formatted with original error message.
 *
 * All other errors will be formatted with generic "Internal server error".
 */
interface ClientAware
{
    /**
     * Returns true when exception message is safe to be displayed to a client.
     *
     * @return bool
     *
     * @api
     */
    public function isClientSafe();
}
