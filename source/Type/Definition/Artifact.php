<?php

namespace Sage\Type\Definition;

abstract class Artifact
{
    public const ATTRIBUTE = 'attribute';
    public const ACT = 'act';
    public const LINK = 'link';

    /** @var string */
    public $name;
    
    /** @var string|null */
    public $description;

    /** @var string|null */
    public $deprecationReason = null;

    /** @var bool */
    public $deprecated = false;

    /**
     * Original type artifact definition settings
     *
     * @var array
     */
    public $settings;

    public function __construct(array $settings)
    {
        $this->name              = $settings['name'];
        $this->description       = $settings['description']       ?? null;
        $this->deprecationReason = $settings['deprecationReason'] ?? null;
        $this->deprecated        = $settings['deprecated']        ?? isset($settings['deprecationReason']);
    }
}
