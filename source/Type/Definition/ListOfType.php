<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\Type\Schema;
use function is_callable;

class ListOfType extends Type implements WrappingType
{
  /** @var callable():Type|Type */
  public $ofType;

  /**
   * @param callable():Type|Type $type
   */
  public function __construct($type)
  {
    $this->ofType = is_callable($type) ? $type : Type::assertType($type);
  }

  public function toString(): string
  {
    return '[' . $this->ofType()->toString() . ']';
  }

  public function ofType()
  {
    return Schema::resolveType($this->ofType);
  }

  /**
   * @return Entity|ScalarType|(Type&WrappingType)
   */
  public function wrappedType(bool $recurse = false): Type
  {
    $type = $this->ofType();

    return $recurse && $type instanceof WrappingType
      ? $type->wrappedType($recurse)
      : $type;
  }
}
