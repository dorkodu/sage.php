<?php

declare(strict_types=1);

namespace Sage\Executor\Promise\Adapter;

use function count;
use Sage\Error\InvariantViolation;
use Sage\Executor\ExecutionResult;
use Sage\Executor\Promise\Promise;
use Sage\Executor\Promise\PromiseAdapter;
use Sage\Utils\Utils;
use Throwable;

/**
 * Allows changing order of artifact resolution even in sync environments
 * (by leveraging queue of deferreds and promises).
 */
class SyncPromiseAdapter implements PromiseAdapter
{
    public function isThenable($value): bool
    {
        return $value instanceof SyncPromise;
    }

    public function convertThenable($thenable): Promise
    {
        if (!$thenable instanceof SyncPromise) {
            // End-users should always use Deferred (and don't use SyncPromise directly)
            throw new InvariantViolation('Expected instance of Sage\Deferred, got '.Utils::printSafe($thenable));
        }

        return new Promise($thenable, $this);
    }

    public function then(Promise $promise, ?callable $onFulfilled = null, ?callable $onRejected = null): Promise
    {
        /** @var SyncPromise $adoptedPromise */
        $adoptedPromise = $promise->adoptedPromise;

        return new Promise($adoptedPromise->then($onFulfilled, $onRejected), $this);
    }

    public function create(callable $resolver): Promise
    {
        $promise = new SyncPromise();

        try {
            $resolver(
                [
                $promise,
                'resolve',
                ],
                [
                $promise,
                'reject',
                ]
            );
        } catch (Throwable $e) {
            $promise->reject($e);
        }

        return new Promise($promise, $this);
    }

    public function createFulfilled($value = null): Promise
    {
        $promise = new SyncPromise();

        return new Promise($promise->resolve($value), $this);
    }

    public function createRejected($reason): Promise
    {
        $promise = new SyncPromise();

        return new Promise($promise->reject($reason), $this);
    }

    public function all(array $promisesOrValues): Promise
    {
        $all = new SyncPromise();

        $total = count($promisesOrValues);
        $count = 0;
        $result = [];

        foreach ($promisesOrValues as $index => $promiseOrValue) {
            if ($promiseOrValue instanceof Promise) {
                $result[$index] = null;
                $promiseOrValue->then(
          static function ($value) use ($index, &$count, $total, &$result, $all): void {
              $result[$index] = $value;
              ++$count;
              if ($count < $total) {
                  return;
              }

              $all->resolve($result);
          },
          [$all, 'reject']
        );
            } else {
                $result[$index] = $promiseOrValue;
                ++$count;
            }
        }

        if ($count === $total) {
            $all->resolve($result);
        }

        return new Promise($all, $this);
    }

    /**
     * Synchronously wait when promise completes.
     *
     * @return ExecutionResult|array<ExecutionResult>
     */
    public function wait(Promise $promise)
    {
        $this->beforeWait($promise);
        $taskQueue = SyncPromise::getQueue();

        while (
      SyncPromise::PENDING === $promise->adoptedPromise->state &&
      !$taskQueue->isEmpty()
    ) {
            SyncPromise::runQueue();
            $this->onWait($promise);
        }

        /** @var SyncPromise $syncPromise */
        $syncPromise = $promise->adoptedPromise;

        if (SyncPromise::FULFILLED === $syncPromise->state) {
            return $syncPromise->result;
        }

        if (SyncPromise::REJECTED === $syncPromise->state) {
            throw $syncPromise->result;
        }

        throw new InvariantViolation('Could not resolve promise');
    }

    /**
     * Execute just before starting to run promise completion.
     */
    protected function beforeWait(Promise $promise): void
    {
    }

    /**
     * Execute while running promise completion.
     */
    protected function onWait(Promise $promise): void
    {
    }
}
