<?php

declare(strict_types=1);

namespace Sage\Validator;

use Sage\Error\Error;
use Sage\Document;
use Sage\Type\Schema;

abstract class SourceValidationContext
{
    /** @var Document */
    protected $source;

    /** @var Error[] */
    protected $errors;

    /** @var Schema */
    protected $schema;

    public function __construct(Document $source, ?Schema $schema = null)
    {
        $this->source    = $source;
        $this->schema = $schema;
        $this->errors = [];
    }

    public function reportError(Error $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return Error[]
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function document(): Document
    {
        return $this->document;
    }

    public function schema(): ?Schema
    {
        return $this->schema;
    }
}
