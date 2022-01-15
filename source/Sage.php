<?php

declare(strict_types=1);

namespace Sage;

use Sage\Document;
use Sage\Error\Error;
use Sage\Type\Schema;
use Sage\Executor\Executor;
use Sage\Executor\ExecutionResult;
use Sage\Executor\Promise\Promise;
use Sage\Executor\ExecutionDirective;
use Sage\Validator\DocumentValidator;
use Sage\Executor\Promise\PromiseAdapter;
use Sage\Executor\Promise\Adapter\SyncPromiseAdapter;

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
   * 
   * document:
   *    A Sage document representing the request. Should be valid, but optional for performance concerns.
   * 
   * context:
   *    (optional)
   *    The context value is provided as an argument to resolver functions after
   *    query. It is used to pass shared information useful at any point during 
   *    query execution, for example the currently logged in user and
   *    connections to databases or other services.
   * 
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
    ?array $context = null,
    ?array $options = null
  ): ExecutionResult {

    $promiseAdapter = new SyncPromiseAdapter();

    $promise = self::promiseToExecute(
      $promiseAdapter,
      $schema,
      $document,
      $context,
      $options
    );

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
  public static function promiseToExecute(
    PromiseAdapter $promiseAdapter,
    Schema $schema,
    Document $document,
    ?array $context = null,
    ?array $options = null
  ): Promise {
    try {

      //? if string, parse the request string and get document.
      /**
       * $document = Parser::parse(new Source($source ?? '', 'GraphQL'));  
       */

      //? validate the document
      $validationErrors = DocumentValidator::validate($schema, $document);

      //? if document is invalid, return an empty execution result with validation errors
      if (count($validationErrors) > 0) {
        return $promiseAdapter->createFulfilled(
          new ExecutionResult(null, $validationErrors)
        );
      }

      //? document is valid, so return the execution result
      return Executor::promiseToExecute(
        $promiseAdapter,
        $schema,
        $document,
        $context
      );
    } catch (Error $e) {
      return $promiseAdapter->createFulfilled(
        new ExecutionResult(null, [$e])
      );
    }
  }
}
