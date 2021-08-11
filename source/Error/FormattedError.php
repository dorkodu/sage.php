<?php

declare(strict_types=1);

namespace Sage\Error;

use Countable;
use ErrorException;
use Sage\Type\Definition\Type;
use Sage\Type\Definition\WrappingType;
use Sage\Utils\Utils;
use Throwable;
use function addcslashes;
use function array_filter;
use function array_intersect_key;
use function array_map;
use function array_merge;
use function array_shift;
use function count;
use function get_class;
use function gettype;
use function implode;
use function is_array;
use function is_bool;
use function is_object;
use function is_scalar;
use function is_string;
use function mb_strlen;
use function preg_split;
use function sprintf;
use function str_repeat;
use function strlen;

/**
 * This class is used for [default error formatting](error-handling.md).
 * It converts PHP exceptions to [spec-compliant errors](https://libre.dorkodu.com/sage/paper/~proposal)
 * and provides tools for error debugging.
 */
class FormattedError
{
  /** @var string */
  private static $internalErrorMessage = 'Internal server error';

  /**
   * Set default error message for internal errors formatted using createFormattedError().
   * This value can be overridden by passing 3rd argument to `createFormattedError()`.
   *
   * @param string $message
   *
   * @api
   */
  public static function setInternalErrorMessage($message)
  {
    self::$internalErrorMessage = $message;
  }

  /**
   * Prints a Sage Error to a string, representing useful information
   * about the error's details.
   *
   * @return string
   */
  public static function print(Error $error)
  {
    $printedLocations = [];

    if (count($error->locations()) !== 0) {
      foreach (($error->locations() ?? []) as $location) {
        $printedLocations[] = self::highlightLocation($location);
      }
    }

    return count($printedLocations) === 0
      ? $error->getMessage()
      : implode("\n\n", array_merge([$error->getMessage()], $printedLocations)) . "\n";
  }

  /**
   * Highlights an ErrorLocation and stringifies it.
   *
   * @param ErrorLocation $location
   * @return string
   */
  public static function highlightLocation(ErrorLocation $location)
  {
    // TODO: Write the string template of an ErrorLocation
    return sprintf('Query "%s" (field: "%s")', $location->query, $location->field);
  }

  /**
   * Standard Sage error formatter. Converts any exception to array
   * conforming to Sage spec.
   *
   * This method only exposes exception message when exception implements ClientAware interface
   * (or when debug flags are passed).
   *
   * For a list of available debug flags @see \Sage\Error\DebugFlag constants.
   *
   * @param Throwable $exception
   * @param int $debug one of DebugFlag constants
   * @param string|null $internalErrorMessage
   *
   * @return mixed[]
   *
   * @throws Throwable
   *
   * @api
   */
  public static function create(Throwable $exception, int $debug = DebugFlag::NONE, $internalErrorMessage = null): array
  {
    $internalErrorMessage = $internalErrorMessage ?? self::$internalErrorMessage;

    if ($exception instanceof ClientAware) {
      $formattedError = [
        'message'  => $exception->isClientSafe() ? $exception->getMessage() : $internalErrorMessage,
        'meta' => [
          'category' => $exception->category(),
        ],
      ];
    } else {
      $formattedError = [
        'message'  => $internalErrorMessage,
        'meta' => [
          'category' => Error::CATEGORY_INTERNAL,
        ],
      ];
    }

    if ($exception instanceof Error) {
      $locations = Utils::map(
        $exception->locations(),
        static function (ErrorLocation $location): array {
          return $location->toSerializableArray();
        }
      );

      if (count($locations) > 0) {
        $formattedError['locations'] = $locations;
      }

      if (count($exception->meta() ?? []) > 0) {
        $formattedError['meta'] = $exception->meta() + $formattedError['extensions'];
      }
    }

    if ($debug !== DebugFlag::NONE) {
      $formattedError = self::addDebugEntries($formattedError, $exception, $debug);
    }

    return $formattedError;
  }

  /**
   * Decorates spec-compliant $formattedError with debug entries according to $debug flags
   * (@see \Sage\Error\DebugFlag for available flags)
   *
   * @param array $formattedError
   * @param Throwable $e
   * @param integer $debugFlag
   * 
   * @throws Throwable
   * 
   * @return array
   */
  public static function addDebugEntries(array $formattedError, Throwable $e, int $debugFlag): array
  {
    if ($debugFlag === DebugFlag::NONE) {
      return $formattedError;
    }

    if (($debugFlag & DebugFlag::RETHROW_INTERNAL_EXCEPTIONS) !== 0) {
      if (!$e instanceof Error) {
        throw $e;
      }

      if ($e->getPrevious() !== null) {
        throw $e->getPrevious();
      }
    }

    $isUnsafe = !$e instanceof ClientAware || !$e->isClientSafe();

    if (($debugFlag & DebugFlag::RETHROW_UNSAFE_EXCEPTIONS) !== 0 && $isUnsafe && $e->getPrevious() !== null) {
      throw $e->getPrevious();
    }

    if (($debugFlag & DebugFlag::INCLUDE_DEBUG_MESSAGE) !== 0 && $isUnsafe) {
      // Displaying debugMessage as a first entry:
      $formattedError = ['debugMessage' => $e->getMessage()] + $formattedError;
    }

    if (($debugFlag & DebugFlag::INCLUDE_TRACE) !== 0) {
      if ($e instanceof ErrorException || $e instanceof \Error) {
        $formattedError += [
          'file' => $e->getFile(),
          'line' => $e->getLine(),
        ];
      }

      $isTrivial = $e instanceof Error && $e->getPrevious() === null;

      if (!$isTrivial) {
        $debugging               = $e->getPrevious() ?? $e;
        $formattedError['trace'] = static::toSafeTrace($debugging);
      }
    }

    return $formattedError;
  }

  /**
   * Prepares final error formatter taking in account $debug flags.
   * If initial formatter is not set, FormattedError::create is used
   */
  public static function prepareFormatter(?callable $formatter, int $debug): callable
  {
    $formatter = $formatter ?? function ($e) {
      return FormattedError::create($e);
    };

    if ($debug !== DebugFlag::NONE) {
      $formatter = function ($e) use ($formatter, $debug) {
        return FormattedError::addDebugEntries($formatter($e), $e, $debug);
      };
    }

    return $formatter;
  }

  /**
   * Returns error trace as serializable array
   *
   * @param Throwable $error
   *
   * @return mixed[]
   *
   * @api
   */
  public static function toSafeTrace($error)
  {
    $trace = $error->getTrace();

    if (
      isset($trace[0]['function']) && isset($trace[0]['class']) &&
      // Remove invariant entries as they don't provide much value:
      ($trace[0]['class'] . '::' . $trace[0]['function'] === 'Sage\Utils\Utils::invariant')
    ) {
      array_shift($trace);
    } elseif (!isset($trace[0]['file'])) {
      // Remove root call as it's likely error handler trace:
      array_shift($trace);
    }

    return array_map(
      static function ($err): array {
        $safeErr = array_intersect_key($err, ['file' => true, 'line' => true]);

        if (isset($err['function'])) {
          $func    = $err['function'];
          $args    = array_map([self::class, 'printVar'], $err['args'] ?? []);
          $funcStr = $func . '(' . implode(', ', $args) . ')';

          if (isset($err['class'])) {
            $safeErr['call'] = $err['class'] . '::' . $funcStr;
          } else {
            $safeErr['function'] = $funcStr;
          }
        }

        return $safeErr;
      },
      $trace
    );
  }

  /**
   * @param mixed $var
   *
   * @return string
   */
  public static function printVar($var)
  {
    if ($var instanceof Type) {
      // TODO: Replace with schema printer call
      if ($var instanceof WrappingType) {
        $var = $var->getWrappedType(true);
      }

      return 'SageType: ' . $var->name;
    }

    if (is_object($var)) {
      return 'instance of ' . get_class($var) . ($var instanceof Countable ? '(' . count($var) . ')' : '');
    }

    if (is_array($var)) {
      return 'array(' . count($var) . ')';
    }

    if ($var === '') {
      return '(empty string)';
    }

    if (is_string($var)) {
      return "'" . addcslashes($var, "'") . "'";
    }

    if (is_bool($var)) {
      return $var ? 'true' : 'false';
    }

    if (is_scalar($var)) {
      return $var;
    }

    if ($var === null) {
      return 'null';
    }

    return gettype($var);
  }

  /**
   * @deprecated as of v0.10.0, use general purpose method create() instead
   *
   * @return mixed[]
   *
   * @codeCoverageIgnore
   */
  public static function createFromPHPError(ErrorException $e)
  {
    return [
      'message'  => $e->getMessage(),
      'severity' => $e->getSeverity(),
      'trace'    => self::toSafeTrace($e),
    ];
  }
}
