<?php

declare(strict_types=1);

namespace Sage\Validator\Rules;

use Sage\Language\AST\Node;
use Sage\Language\VisitorOperation;
use Sage\Validator\SDLValidationContext;
use Sage\Validator\ValidationContext;

use function class_alias;

abstract class ValidationRule
{
    protected string $name;

    public function getName(): string
    {
        return $this->name === '' || $this->name === null
            ? static::class
            : $this->name;
    }

    /**
     * @return array<string, callable(Node): VisitorOperation|mixed|null>|array<string, array<string, callable(Node): VisitorOperation|mixed|null>>
     */
    public function __invoke(ValidationContext $context): array
    {
        return $this->getVisitor($context);
    }

    /**
     * Returns structure suitable for Sage\Language\Visitor
     *
     * @see \Sage\Language\Visitor
     *
     * @return array<string, callable(Node): VisitorOperation|mixed|null>|array<string, array<string, callable(Node): VisitorOperation|mixed|null>>
     */
    public function getVisitor(ValidationContext $context): array
    {
        return [];
    }

    /**
     * Returns structure suitable for Sage\Language\Visitor
     *
     * @see \Sage\Language\Visitor
     *
     * @return array<string, callable(Node): VisitorOperation|mixed|null>|array<string, array<string, callable(Node): VisitorOperation|mixed|null>>
     */
    public function getSDLVisitor(SDLValidationContext $context): array
    {
        return [];
    }
}

class_alias(ValidationRule::class, 'Sage\Validator\Rules\AbstractValidationRule');
