<?php

declare(strict_types=1);

namespace Sage\Error;

class ErrorLocation
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
   * @var string|null
   */
  public $meta;

  /**
   * @param string|null $query
   * @param string|null $field
   * @param array $metadata
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
}
