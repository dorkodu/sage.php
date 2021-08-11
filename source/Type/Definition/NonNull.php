<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\Error\InvariantViolation;
use Sage\Type\Schema;
use function is_callable;

class NonNull extends Type implements WrappingType
{
  /** @var callable|Type */
  private $ofType;

  /**
   * @param  Type|callable $type
   */
  public function __construct($type)
  {
    $nullableType = $type;
    $this->ofType = $nullableType;
  }

  public function toString(): string
  {
    return $this->wrappedType()->toString() . '!';
  }

  public function ofType()
  {
    return Schema::resolveType($this->ofType);
  }

  public function wrappedType(bool $recurse = false): Type
  {
    $type = $this->ofType();

    return $recurse && $type instanceof WrappingType
      ? $type->wrappedType($recurse)
      : $type;
  }
}
