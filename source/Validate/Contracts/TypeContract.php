<?php

namespace Sage\Validate\Contracts;

use ReflectionClass;

class TypeContract extends Contract
{
    /**
     * @return string|null
     */
    protected function tryInferName()
    {
        if ($this->name) {
            return $this->name;
        }

        /*
         ? If class is extended - infer name from className
         ? QueryType --> Type
         ? SomeOtherType --> SomeOther
         */
        $tmp = new ReflectionClass($this);
        $name = $tmp->getShortName();

        if (__NAMESPACE__ !== $tmp->getNamespaceName()) {
            return preg_replace('~Type$~', '', $name);
        }

        return null;
    }  
}