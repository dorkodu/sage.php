<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\Type\Definition\Attribute;
use Sage\Type\Definition\Act;
use Sage\Type\Definition\Link;
use Sage\Error\InvariantViolation;
use Sage\Utils\Utils;
use function is_callable;
use function sprintf;

/**
 * Entity Definition
 *
 * Almost everything you define will be Entity types.
 * Entities are composite types, which contain artifacts (attributes, acts and links).
 *
 * Example:
 *
 *   $AddressType = new Entity([
 *     'name' => "Address",
 *     'attributes' => [
 *       'street' => $streetAttribute,
 *       'number' => $numberAttribute
 *     ]
 *   ]);
 */
class Entity
{
    /** @var string */
    public $name;

    /** @var string|null */
    public $description;

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
    public $settings;

    /**
     * @param mixed[] $settings
     */
    public function __construct(array $settings)
    {
        $this->name              = $settings['name'];
        $this->description       = $settings['description'] ?? null;
        $this->deprecationReason = $settings['deprecationReason'] ?? null;
        $this->deprecated        = $settings['deprecated'] ?? (isset($settings['deprecationReason']) ? true : false);

        $this->resolve = $settings['resolve'] ?? null;

        $this->config = $settings;

        $this->attributes = $settings['attributes'] ?? [];
        $this->acts = $settings['acts'] ?? [];
        $this->links = $settings['links'] ?? [];
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
     * @throws InvariantViolation
     */
    public function act(string $name): Act
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
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
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
     * @param string $name
     * @return boolean
     */
    public function hasLink(string $name)
    {
        return isset($this->links[$name]);
    }

    /**
     * Validates type configuration and throws if one of options is invalid.
     *
     * @throws InvariantViolation
     *
     * @return void
     */
    public function assertValid()
    {
        parent::assertValid();

        //? Assert: all resolver function is valid.
        $this->assertResolveIsValid();

        //? Assert: all artifacts are valid.
        $this->assertAttributesAreValid();
        $this->assertActsAreValid();
        $this->assertLinksAreValid();
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

    public function assertAttributesAreValid()
    {
        //? Assert: all attributes are valid.
        foreach ($this->attributes as $name => $attribute) {
            Utils::invariant(
                $attribute instanceof Attribute,
                "%s - 'attributes' must be a map of Attribute instances as values, but contains invalid item(s).",
                $this->name
            );

            Utils::invariant(
                is_string($name),
                "%s - 'attributes' must be a map of string names as keys, but contains invalid key(s).",
                $this->name
            );

            $attribute->assertValid($this);
        }
    }

    public function assertActsAreValid()
    {
        //? Assert: all acts are valid.
        foreach ($this->acts as $name => $act) {
            Utils::invariant(
                $act instanceof Act,
                "%s - 'acts' must be a map of Act instances as values, but contains invalid item(s).",
                $this->name
            );

            Utils::invariant(
                is_string($name),
                "%s - 'acts' must be a map of string names as keys, but contains invalid key(s).",
                $this->name
            );

            $act->assertValid($this);
        }
    }

    public function assertLinksAreValid()
    {
        //? Assert: all links are valid.
        foreach ($this->links as $name => $link) {
            Utils::invariant(
                $link instanceof Link,
                "%s - 'links' must be a map of Link instances as values, but contains invalid item(s).",
                $this->name
            );

            Utils::invariant(
                is_string($name),
                "%s - 'links' must be a map of string names as keys, but contains invalid key(s).",
                $this->name
            );

            $link->assertValid($this);
        }
    }
}
