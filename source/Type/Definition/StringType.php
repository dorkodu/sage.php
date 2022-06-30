<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Exception;
use Sage\Error\Error;
use Sage\Utils\Utils;
use function is_object;
use function is_scalar;
use function is_string;
use function method_exists;

class StringType extends ScalarType
{
  /** @var string */
  public $name = Type::STRING;

  /** @var string */
  public $description =
  'The `String` scalar type represents textual data, represented as UTF-8
character sequences. The String type is most often used by Sage to
represent free-form human-readable text.';

  /**
   * @param mixed $value
   *
   * @return mixed|string
   *
   * @throws Error
   */
  public function serialize($value)
  {
    $canCast = is_scalar($value)
      || (is_object($value) && method_exists($value, '__toString'))
      || $value === null;

    if (!$canCast) {
      throw new Error(
        'String cannot represent value: ' . Utils::printSafe($value)
      );
    }

    return (string) $value;
  }

  /**
   * @param mixed $value
   *
   * @return string
   *
   * @throws Error
   */
  public function parseValue($value)
  {
    if (!is_string($value)) {
      throw new Error(
        'String cannot represent a non string value: ' . Utils::printSafe($value)
      );
    }

    return $value;
  }
}
