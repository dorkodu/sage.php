<?php

namespace Blog;

use Sage\Type\Definition\Act;
use Sage\Type\Definition\Link;
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Attribute;

class TypeRegistry
{
  /** @var array<string, Entity> */
  private static array $entities = [];

  /** @var array<string, Attribute> */
  private static array $attributes = [];

  /** @var array<string, Act> */
  private static array $acts = [];

  /** @var array<string, Link> */
  private static array $links = [];

  public static function entity(string $name): Entity
  {
    return self::$entities[$name];
  }

  public static function attribute(string $name): Attribute
  {
    return self::$attributes[$name];
  }

  public static function act(string $name): Act
  {
    return self::$acts[$name];
  }

  public static function link(string $name): Link
  {
    return self::$links[$name];
  }
}
