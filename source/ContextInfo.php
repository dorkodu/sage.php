<?php

declare(strict_types=1);

namespace Sage;

use Sage\Query;
use Sage\Type\Schema;
use Sage\Type\Definition\Artifact;
use Sage\Type\Definition\Entity;

/**
 * Structure containing information useful for data resolution process.
 *
 * Passed as second argument to every entity and artifact function.
 * See [docs on artifact resolving (data fetching)](data-fetching.md).
 */
class ContextInfo
{
    /**
     * The definition of the artifact being resolved.
     *
     * @api
     * @var Artifact|null
     */
    public $artifact;

    /**
     * Parent type (Entity) of the artifact being resolved.
     *
     * @api
     * @var Entity
     */
    public $entity;

    /**
     * Instance of a schema used for execution.
     *
     * @api
     * @var Schema
     */
    public $schema;

    /**
     * Instance of the query requested to be executed.
     *
     * @api
     * @var Query
     */
    public $query;

    /**
     * A map given for use as the context of the service, like a simple dependency container.
     *
     * @api
     * @var mixed[]
     */
    public $value;

    public function __construct(
        ?Artifact $artifact,
        Entity $entity,
        Schema $schema,
        Query $query,
        array $value = []
    ) {
        $this->artifact   = $artifact;
        $this->entity     = $entity;
        $this->schema     = $schema;
        $this->query      = $query;
        $this->value      = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    //? ContextInfo must be immutable, this is the way I know how to do so.
    public function __set($name, $value)
    {
    }
}
