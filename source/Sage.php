<?php

declare(strict_types=1);

namespace Sage;

use Sage\Executor\ExecutionResult;
use Sage\Executor\Promise\Adapter\SyncPromiseAdapter;
use Sage\Executor\Promise\Promise;
use Sage\Executor\Promise\PromiseAdapter;
use Sage\Executor\ExecutionDirective;
use Sage\Document;
use Sage\Type\Schema;

/**
 * This is the primary facade for Sage runtime.
 * See [related documentation](/docs/executing-queries.md).
 */
class Sage
{
  /**
   * Executes a Sage document.
   *
   * More sophisticated Sage servers, such as those which persist queries,
   * may wish to separate the validation and execution phases to a static time
   * tooling step, and a server runtime step.
   *
   * - Parameters:
   *
   * schema:
   *    The Sage type system schema to use when validating and executing a query.
   * document:
   *    A Sage document representing the request.
   * context:
   *    (optional)
   *    The context value is provided as an argument to resolver functions after
   *    query. It is used to pass shared information useful at any point during 
   *    query execution, for example the currently logged in user and
   *    connections to databases or other services.
   * options:
   *    (optional)
   *    An associative array representing the settings for query execution.
   * 
   * @param Schema $schema
   * @param Document $document
   * @param mixed $context
   * @param array $options
   *
   * @api
   */
  public static function execute(
    Schema $schema,
    Document $document,
    $context = null,
    $options = null
  ): ExecutionResult {

    $executionDirective = new ExecutionDirective(
      $schema,
      $document,
      $context,
      $options
    );

    $promise = self::promiseToExecute($executionDirective);

    $promiseAdapter = new SyncPromiseAdapter();

    return $promiseAdapter->wait($promise);
  }

  /**
   * Requires PromiseAdapter and always returns a Promise.
   * Useful for Async PHP platforms.
   *
   * @param ExecutionDirective $directive
   * 
   * @api
   */
  public static function promiseToExecute(ExecutionDirective $directive): Promise
  {
  }

  public static function setPromiseAdapter(?PromiseAdapter $promiseAdapter = null): void
  {
  }
}
