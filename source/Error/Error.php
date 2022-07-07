<?php

declare(strict_types=1);

namespace Sage\Error;

use Exception;
use Throwable;
use Traversable;
use JsonSerializable;
use Sage\Utils\Utils;
use Sage\Error\ClientAware;
use function count;
use function is_array;
use function array_map;
use function array_filter;
use function array_values;
use function iterator_to_array;

/**
 * Describes an Error found during the validation or
 * execution phases of performing a Sage operation. In addition to a message
 * and stack trace, it also includes information about the locations in a
 * Sage document and/or execution result that correspond to the Error.
 *
 * When the error was caused by an exception thrown in resolver, original exception
 * is available via `getPrevious()`.
 *
 * Also read related docs on [error handling](error-handling.md)
 *
 * Class extends standard PHP `\Exception`, so all standard methods of base `\Exception` class
 * are available in addition to those listed below.
 */
class Error extends Exception implements JsonSerializable, ClientAware
{
  const CATEGORY_SAGE  = 'sage';
  const CATEGORY_INTERNAL = 'internal';

  /**
   * The source Sage document for the first location of this error.
   *
   * Note that if this Error represents more than one node, the source may not
   * represent nodes after the first node.
   *
   * @var Document|null
   */
  private $source;

  /** @var ErrorLocation[] */
  private $locations;

  /** @var bool */
  private $isClientSafe;

  /** @var string */
  protected $category;

  /** @var mixed[]|null */
  protected $meta;

  /**
   * @param string $message
   * @param ErrorLocation[]|null $locations
   * @param Throwable $previous
   * @param mixed[] $meta
   */
  public function __construct(
    $message = '',
    $locations = null,
    $previous = null,
    array $meta = []
  ) {
    parent::__construct($message, 0, $previous);

    $this->locations  = $locations;

    $this->meta = count($meta) > 0
      ? $meta
      : ($previous instanceof self ? $previous->meta : []);

    if ($previous instanceof ClientAware) {
      $this->isClientSafe = $previous->isClientSafe();
      $cat                = $previous->category();
      $this->category     = $cat === '' || $cat === null  ? self::CATEGORY_INTERNAL : $cat;
    } elseif ($previous !== null) {
      $this->isClientSafe = false;
      $this->category     = self::CATEGORY_INTERNAL;
    } else {
        $this->isClientSafe = true;
        $this->category     = self::CATEGORY_SAGE;
    }
  }

  /**
   * Given an arbitrary Error, presumably thrown while attempting to execute a
   * Sage operation, produce a new SageError aware of the location in the
   * document responsible for the original Error.
   *
   * @param mixed        $error
   * @param ErrorLocation[]|null  $locations
   *
   * @return Error
   */
  public static function createLocatedError($error, $locations = null)
  {
    if ($error instanceof self) {
      if ($error->path !== null && $error->nodes !== null && count($error->nodes) !== 0) {
        return $error;
      }

      $nodes = $nodes ?? $error->nodes;
      $path  = $path ?? $error->path;
    }

    $source        = null;
    $originalError = null;
    $positions     = [];
    $meta    = [];

    if ($error instanceof self) {
      $message       = $error->getMessage();
      $originalError = $error;
      $nodes         = $error->nodes ?? $nodes;
      $source        = $error->source;
      $positions     = $error->positions;
      $meta    = $error->meta;
    } elseif ($error instanceof Throwable) {
      $message       = $error->getMessage();
      $originalError = $error;
    } else {
      $message = (string) $error;
    }

  return new static(
      $message === '' || $message === null ? 'An unknown error occurred.' : $message,
      $nodes,
      $source,
      $positions,
      $path,
      $originalError,
      $meta
    );
  }

  /**
   * @inheritdoc
   */
  public function isClientSafe()
  {
    return $this->isClientSafe;
  }

  /**
   * @inheritdoc
   */
  public function category()
  {
    return $this->category;
  }

  /**
   * An array of locations within the source Sage document which correspond to this error.
   *
   * Each entry has information about query name and the requested artifact's name with given artifact type within source Sage document:
   *
   * $location->query;
   * $location->artifact;
   *
   * Errors during execution include a single location, the query field which produced the error.
   *
   * @return ErrorLocation[]
   *
   * @api
   */
  public function locations()
  {
    return $this->locations;
  }

  /**
   * @return mixed[]
   */
  public function meta()
  {
    return $this->meta;
  }

  /**
   * Specify data which should be serialized to JSON
   *
   * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
   *
   * @return mixed data which can be serialized by **json_encode**,
   * which is a value of any type other than a resource.
   */
  public function jsonSerialize()
  {
    $arr = [
      'message' => $this->getMessage(),
    ];

    $locations = Utils::map(
    $this->locations(),
      function (ErrorLocation $loc) {
          return $loc->toSerializableArray();
      }
    );

    if (count($locations) > 0) {
      $arr['location'] = $locations;
    }

    if (count($this->meta ?? []) > 0) {
      $arr['meta'] = $this->meta;
    }

    return $arr;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return FormattedError::print($this);
  }
}
