<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Exception;

use Sage\Error\Error;
use Sage\Utils\Utils;

use function is_bool;

class BooleanType extends ScalarType
{
  /** @var string */
  public $name = Type::BOOLEAN;

  /** @var string */
  public $description = 'The `Boolean` scalar type represents `true` or `false`.';

  /**
   * Serialize the given value to a boolean.
   *
   * The Sage spec leaves this up to the implementations, so we just do what
   * PHP does natively to make this intuitive for developers.
   *
   * @param mixed $value
   */
  public function serialize($value): bool
  {
    return (bool) $value;
  }

  /**
   * @param mixed $value
   *
   * @return bool
   *
   * @throws Error
   */
  public function parseValue($value)
  {
    if (is_bool($value)) {
      return $value;
    }

    throw new Error('Boolean cannot represent a non boolean value: ' . Utils::printSafe($value));
  }
}
