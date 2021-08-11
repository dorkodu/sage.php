<?php

declare(strict_types=1);

namespace Sage\Error;

use JsonSerializable;

class ErrorLocation implements JsonSerializable
{
  public const ATTRIBUTE = "attribute";
  public const LINK = "link";
  public const ACT = "act";

  /**
   * Name of the query which this error occured.
   *
   * @var string|null
   */
  public $query;

  /**
   * The field in query which this error occured.
   *
   * @var string|null
   */
  public $field;

  /**
   * Metadata about this error.
   * e.g. name of the field
   *
   * @var array|null
   */
  public $meta;

  /**
   * @param string|null $query
   * @param string|null $field
   * @param array|null $metadata
   */
  public function __construct($query = null, $field = null, array $meta = null)
  {
    $this->query = $query;
    $this->field = $field;
    $this->meta = $meta;
  }

  public function __get($name)
  {
    switch ($name) {
      case "query":
        return $this->query;
      case "field":
        return $this->field;
      case "meta":
        return $this->meta;
      default:
        break;
    }
  }

  public function __set($name, $value)
  {
  }

  /**
   * @return int[]
   */
  public function toArray()
  {
    return [
      'query'   => $this->query,
      'field' => $this->field,
      'meta' => $this->meta
    ];
  }

  /**
   * @return int[]
   */
  public function toSerializableArray()
  {
    return $this->toArray();
  }

  /**
   * @return int[]
   */
  public function jsonSerialize()
  {
    return $this->toSerializableArray();
  }
}
