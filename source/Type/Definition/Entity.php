<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\ContextInfo;
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
 * Entities are composite types, which contain artifacts (attributes, acts and links).
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
  /** @var callable */
  public $resolve;

  /** @var Attribute[] */
  public $attributes;

  /** @var Act[] */
  public $acts;

  /** @var Link[] */
  public $links;

  /** @var bool */
  public $deprecated = false;

  /** @var string|null */
  public $deprecationReason;

  /**
   * Original type definition configuration
   *
   * @var array
   */
  public $config;

  /**
   * @param mixed[] $config
   */
  public function __construct(array $config)
  {
    $this->name              = $config['name'];
    $this->description       = $config['description'] ?? null;
    $this->deprecationReason = $config['deprecationReason'] ?? null;
    $this->deprecated        = $config['deprecated'] ?? (isset($config['deprecationReason']) ? true : false);

    $this->resolve = $config['resolve'] ?? null;

    $this->config = $config;

    // TODO: add attributes, acts and links
  }

  /**
   * @param mixed $type
   *
   * @return $this
   *
   * @throws InvariantViolation
   */
  public static function assertEntityType($type)
  {
    Utils::invariant(
      $type instanceof self,
      'Expected ' . Utils::printSafe($type) . ' to be a Sage Entity type.'
    );
  }

  /**
   * @throws InvariantViolation
   */
  public function attribute(string $name): Attribute
  {
    Utils::invariant(
      isset($this->attributes[$name]),
      'Attribute "%s" is not defined for Entity "%s"',
      $name,
      $this->name
    );

    return $this->attributes[$name];
  }

  /**
   * @param string $name
   * @return boolean
   */
  public function hasAttribute(string $name): bool
  {
    return isset($this->attributes[$name]);
  }

  /**
   * @throws InvariantViolation
   */
  public function act(string $name)
  {
    Utils::invariant(
      isset($this->acts[$name]),
      'Act "%s" is not defined for Entity "%s"',
      $name,
      $this->name
    );

    return $this->acts[$name];
  }

  /**
   * @param string $name
   * @return boolean
   */
  public function hasAct(string $name)
  {
    return isset($this->acts[$name]);
  }

  /**
   * @throws InvariantViolation
   */
  public function link(string $name): Link
  {
    Utils::invariant(
      isset($this->links[$name]),
      'Link "%s" is not defined for Entity "%s"',
      $name,
      $this->name
    );

    return $this->links[$name];
  }

  /**
   * @param string $name
   * @return boolean
   */
  public function hasLink(string $name)
  {
    return isset($this->links[$name]);
  }

  /**
   * Validates type config and throws if one of type options is invalid.
   * Note: this method is shallow, it won't validate object fields and their arguments.
   *
   * @throws InvariantViolation
   * 
   * @return void
   */
  public function assertValid()
  {
    parent::assertValid();

    $this->assertResolveIsValid();

    //? Assert: all attributes, acts and links are valid.
    foreach ($this->attributes as $attribute) {
      $attribute->assertValid($this);
    }
    foreach ($this->acts as $act) {
      $act->assertValid($this);
    }
    foreach ($this->links as $link) {
      $link->assertValid($this);
    }
  }

  public function assertResolveIsValid()
  {
    //? Assert: resolve is a callable
    Utils::invariant(
      is_callable($this->resolve),
      sprintf(
        '%s - Entity resolver must be a function returning a reference value map, but got: %s',
        $this->name,
        Utils::printSafe($this->resolve)
      )
    );
  }
}
