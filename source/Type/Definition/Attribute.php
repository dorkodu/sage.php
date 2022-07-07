<?php

declare(strict_types=1);

namespace Sage\Type\Definition;

use Sage\Type\Definition\Artifact;
use Sage\Error\InvariantViolation;
use Sage\Utils\Utils;
use function sprintf;
use function is_callable;

class Attribute extends Artifact
{
    /**
     * Callback for resolving attribute value given reference value.
     *
     * @var callable
     */
    public $resolve;

    /**
     * Callback for additional constraint on attribute output value.
     *
     * @var callable|null
     */
    public $rule;

    /**
     * @param mixed[] $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);

        $this->type    = $settings['type']    ?? null;
        $this->resolve = $settings['resolve'] ?? null;
        $this->rule    = $settings['rule']    ?? null;
    }
}
