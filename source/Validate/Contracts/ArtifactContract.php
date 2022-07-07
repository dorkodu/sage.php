<?php

namespace Sage\Validate\Contracts;

use Sage\Utils\Utils;
use Sage\Type\Definition\Type;
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Attribute;
use Sage\Type\Definition\OutputType;
use Sage\Validate\Contracts\Contract;
use Sage\Type\Definition\WrappingType;

/** */
class ArtifactContract extends Contract
{
    /**
     * @throws InvariantViolation
     */
    public static function attribute(Attribute $attribute, Entity $entity)
    {
        self::attributeName($attribute, $entity);
        self::attributeResolver($attribute, $entity);
        self::attributeRule($attribute);
    }

    public static function attributeRule(Attribute $attribute)
    {
        //? Assert: resolve is a callable
        Utils::error(
          $attribute->rule == null || is_callable($attribute->rule),
          sprintf(
              '%s.%s - Attribute rule must be a function, but got: %s',
              $entity->name,
              $attribute->name,
              Utils::printSafe($attribute->resolve)
          )
      );
    }

    public static function attributeName(Attribute $attribute, Entity $entity)
    {

    }

    public static function attributeResolver(Attribute $attribute, Entity $entity)
    {
        //? Assert: resolve is a callable
        Utils::invariant(
            is_callable($attribute->resolve),
            sprintf(
                '%s.%s - Attribute resolver must be a function, but got: %s',
                $entity->name,
                $attribute->name,
                Utils::printSafe($attribute->resolve)
            )
        );
    }
}
