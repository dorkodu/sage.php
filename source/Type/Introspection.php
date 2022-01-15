<?php

declare(strict_types=1);

namespace Sage\Type;

use Exception;
use Sage\Sage;
use Sage\ContextInfo;
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Artifact;
use Sage\Type\Definition\Attribute;
use Sage\Type\Definition\Act;
use Sage\Type\Definition\Link;
use Sage\Type\Definition\ListOfType;
use Sage\Type\Definition\NonNull;
use Sage\Type\Definition\ScalarType;
use Sage\Type\Definition\Type;
use Sage\Type\Definition\WrappingType;
use Sage\Utils\Utils;
use function array_filter;
use function array_key_exists;
use function array_merge;
use function array_values;
use function is_bool;
use function method_exists;
use function trigger_error;
use const E_USER_DEPRECATED;

class Introspection
{
  const META_SCHEMA_NAME = '@Schema';
  const META_TYPE_NAME   = '@Type';

  /** @var array<string, mixed> */
  private static $map = [];

  /**
   * @param Type $type
   *
   * @return bool
   */
  public static function isIntrospectionType($type)
  {
    return array_key_exists($type->name, self::types());
  }

  public static function types()
  {
    //? delay the development of introspection, not number one priority.
    return [
      '@Schema'             => self::Schema(),
      '@Entity'               => self::Entity(),
      '@Attribute'          => self::Attribute(),
      '@Act'             => self::Act(),
      '@Link'             => self::Link(),
    ];
  }

  public static function Schema()
  {
    if (!isset(self::$map['@Schema'])) {
      self::$map['@Schema'] = new Entity([
        'name'            => '@Schema',
        'description'     =>
        'A Sage Schema defines the capabilities of a Sage ' .
          'server. It exposes all available types on ' .
          'the server.',
        'attributes' => [
          'entities' => new Attribute([
            'name' => '@Schema',
            'description' => 'A list of the names of all entity types supported by this server.',
            'resolve'     => static function (Schema $schema): array {
              return array_values($schema->getTypeMap());
            },
          ]),
          'queryType'        => [
            'description' => 'The type that query operations will be rooted at.',
            'type'        => new NonNull(self::_type()),
            'resolve'     => static function (Schema $schema): ?ObjectType {
              return $schema->getQueryType();
            },
          ],
          'mutationType'     => [
            'description' =>
            'If this server supports mutation, the type that ' .
              'mutation operations will be rooted at.',
            'type'        => self::_type(),
            'resolve'     => static function (Schema $schema): ?ObjectType {
              return $schema->getMutationType();
            },
          ],
          'subscriptionType' => [
            'description' => 'If this server support subscription, the type that subscription operations will be rooted at.',
            'type'        => self::_type(),
            'resolve'     => static function (Schema $schema): ?ObjectType {
              return $schema->getSubscriptionType();
            },
          ],
          'directives'       => [
            'description' => 'A list of all directives supported by this server.',
            'type'        => Type::nonNull(Type::listOf(Type::nonNull(self::_directive()))),
            'resolve'     => static function (Schema $schema): array {
              return $schema->getDirectives();
            },
          ],
        ],
      ]);
    }

    return self::$map['@Schema'];
  }

  public static function Entity()
  {
    if (!isset(self::$map['@Entity'])) {
      self::$map['@Entity'] = new Entity([
        'name'            => '@Entity',
        'description'     =>
        'The fundamental unit of any Sage Schema is the Entity type. There are ' .
          'many kinds of types in Sage as represented by the `__TypeKind` enum.' .
          "\n\n" .
          'Depending on the kind of a type, certain attributes describe ' .
          'information about that type. Scalar types provide no information ' .
          'beyond a name and description, while Enum types provide their values. ' .
          'Object and Interface types provide the fields they describe. Abstract ' .
          'types, Union and Interface, provide the Object types possible ' .
          'at runtime. List and NonNull types compose other types.',
        'fields' => static function () {
        },
      ]);
    }

    return self::$map['@Entity'];
  }

  public static function Attribute()
  {
    if (!isset(self::$map['@Attribute'])) {
      self::$map['@Attribute'] = new Entity([
        'name' => '@Attribute',
        'description' =>
        'Entity types contain a list of attributes, each of ' .
          'which has a name, and a return type (optional).',
        'attributes' => static function () {
          return [
            'name' => [
              'type' => Type::nonNull(Type::string()),
              'resolve' => static function (Artifact $artifact): string {
                return $field->name;
              },
            ],
            'description'       => [
              'type' => Type::string(),
              'resolve' => static function (FieldDefinition $field): ?string {
                return $field->description;
              },
            ],
            'args'              => [
              'type'    => Type::nonNull(Type::listOf(Type::nonNull(self::_inputValue()))),
              'resolve' => static function (FieldDefinition $field): array {
                return $field->args ?? [];
              },
            ],
            'type'              => [
              'type'    => Type::nonNull(self::_type()),
              'resolve' => static function (FieldDefinition $field): Type {
                return $field->getType();
              },
            ],
            'isDeprecated'      => [
              'type'    => Type::nonNull(Type::boolean()),
              'resolve' => static function (FieldDefinition $field): bool {
                return (bool) $field->deprecationReason;
              },
            ],
            'deprecationReason' => [
              'type'    => Type::string(),
              'resolve' => static function (FieldDefinition $field): ?string {
                return $field->deprecationReason;
              },
            ],
          ];
        },
      ]);
    }

    return self::$map['__Field'];
  }

  public static function Act()
  {
  }

  public static function Link()
  {
  }
}
