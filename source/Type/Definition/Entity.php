<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\Deferred;
use Sage\Error\InvariantViolation;
use Sage\Type\Schema;
use Sage\Utils\Utils;
use function array_map;
use function is_array;
use function is_callable;
use function is_string;
use function sprintf;

/**
 * Entity Type Definition
 *
 * Almost all of the Sage types you define will be Entity types.
 * Entity types have attributes, acts and links.
 *
 * Example:
 *
 *   $AddressType = new Entity([
 *     'attributes' => [
 *       'street' => $streetAttribute,
 *       'number' => $numberAttribute
 *     ]
 *   ]);
 */
class Entity extends Type
{
  /** @var callable|null */
  public $resolver;

  /** @var Attribute[] */
  private $attributes;

  /** @var Act[] */
  private $acts;

  /** @var Link[] */
  private $links;

  /** @var string */
  private $description;

  /** @var Constraint[] */
  private $constraints;

  /**
   * @param mixed[] $config
   */
  public function __construct(array $config)
  {
    $this->name        = $config['name'];
    $this->description = $config['description'] ?? null;
  }

  /**
   * @param mixed $type
   *
   * @return $this
   *
   * @throws InvariantViolation
   */
  public static function assertEntityType($type): self
  {
    Utils::invariant(
      $type instanceof self,
      'Expected ' . Utils::printSafe($type) . ' to be a Sage Entity type.'
    );

    return $type;
  }

  /**
   * @throws InvariantViolation
   */
  public function attribute(string $name): Attribute
  {
    if (!isset($this->attributes)) {
      $this->initializeFields();
    }

    Utils::invariant(isset($this->fields[$name]), 'Attribute "%s" is not defined for type "%s"', $name, $this->name);

    return $this->attributes[$name];
  }

  /**
   * @param string $name
   * @return boolean
   */
  public function hasAttribute(string $name): bool
  {
    if (!isset($this->attributes)) {
      $this->initializeAttributes();
    }

    return isset($this->attributes[$name]);
  }

  /**
   * @return Attributes[]
   *
   * @throws InvariantViolation
   */
  public function attributes(): array
  {
    if (!isset($this->attributes)) {
      $this->initializeAttributes();
    }

    return $this->attributes;
  }

  protected function initializeAttributes(): void
  {
    $fields       = $this->config['fields'] ?? [];
    $this->fields = Attribute::defineFieldMap($this, $fields);
  }

  /**
   * @param mixed $value
   * @param mixed $context
   *
   * @return bool|Deferred|null
   */
  public function isTypeOf($value, $context, ResolveInfo $info)
  {
    return isset($this->config['isTypeOf'])
      ? $this->config['isTypeOf']($value, $context, $info)
      : null;
  }

  /**
   * Validates type config and throws if one of type options is invalid.
   * Note: this method is shallow, it won't validate object fields and their arguments.
   *
   * @throws InvariantViolation
   */
  public function assertValid(): void
  {
    parent::assertValid();

    // Assert: description must be a string.
    Utils::invariant(
      $this->description === null || is_string($this->description),
      sprintf(
        '%s description must be string if set, but it is: %s',
        $this->name,
        Utils::printSafe($this->description)
      )
    );

    $isTypeOf = $this->config['isTypeOf'] ?? null;

    # Assert: definition must be a string.
    Utils::invariant(
      $isTypeOf === null || is_callable($isTypeOf),
      sprintf('%s must provide "isTypeOf" as a function, but got: %s', $this->name, Utils::printSafe($isTypeOf))
    );

    foreach ($this->attributes() as $attribute) {
      $attribute->assertValid($this);
    }
  }
}
