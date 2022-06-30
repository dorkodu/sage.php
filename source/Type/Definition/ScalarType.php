<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\Utils\Utils;
use function is_string;

/**
 * Scalar Type Definition
 *
 * Scalars are defined with a name and a series of coercion
 * functions used to ensure validity.
 *
 * Example:
 *
 *   class OddType extends ScalarType
 *   {
 *     public $name = 'Odd',
 *     public function serialize($value)
 *     {
 *       return $value % 2 === 1 ? $value : null;
 *     }
 *   }
 */

abstract class ScalarType extends Type
{
  /**
   * @param mixed[] $config
   */
  public function __construct(array $config = [])
  {
    $this->name              = $config['name'] ?? $this->tryInferName();
    $this->description       = $config['description'] ?? $this->description;
    $this->config            = $config;

    Utils::invariant(is_string($this->name), 'Must provide name.');
  }
}
