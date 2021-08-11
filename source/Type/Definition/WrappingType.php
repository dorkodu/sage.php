<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

interface WrappingType
{
  public function wrappedType(bool $recurse = false): Type;
}
