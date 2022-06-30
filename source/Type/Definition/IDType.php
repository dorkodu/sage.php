<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\Error\Error;
use Sage\Utils\Utils;
use function is_int;
use function is_object;
use function is_string;
use function method_exists;

class IDType extends ScalarType
{
  /** @var string */
  public $name = 'ID';

  /** @var string */
  public $description =
  'The `ID` scalar type represents a unique identifier, often used to
refetch an object or as key for a cache. The ID type appears in a JSON
response as a String; however, it is not intended to be human-readable.';

  /**
   * @param mixed $value
   *
   * @return string
   *
   * @throws Error
   */
  public function serialize($value)
  {
    $canCast = is_string($value)
      || is_int($value)
      || (is_object($value) && method_exists($value, '__toString'));

    if (!$canCast) {
      throw new Error('ID cannot represent value: ' . Utils::printSafe($value));
    }

    return (string) $value;
  }

  /**
   * @param mixed $value
   *
   * @throws Error
   */
  public function parseValue($value): string
  {
    if (is_string($value) || is_int($value)) {
      return (string) $value;
    }
    throw new Error('ID cannot represent value: ' . Utils::printSafe($value));
  }
}
