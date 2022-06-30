<?php

declare(strict_types=1);

namespace Sage\Executor\Promise;

use Sage\Executor\Promise\Adapter\SyncPromise;
use Sage\Utils\Utils;

/**
 * Convenience wrapper for promises represented by Promise Adapter.
 */
class Promise
{
    /** @var SyncPromise */
    public $adoptedPromise;

    /** @var PromiseAdapter */
    private $adapter;

    /**
     * @param mixed $adoptedPromise
     */
    public function __construct($adoptedPromise, PromiseAdapter $adapter)
    {
        Utils::invariant(!$adoptedPromise instanceof self, 'Expecting promise from adapted system, got '.self::class);

        $this->adapter = $adapter;
        $this->adoptedPromise = $adoptedPromise;
    }

    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): Promise
    {
        return $this->adapter->then($this, $onFulfilled, $onRejected);
    }
}
