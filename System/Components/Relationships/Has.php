<?php

namespace System\Components\Relationships;

use System\Services\TextTransforms;
use System\Components\Model;
use ReflectionClass;

/**
 * Convenience class for categorization
 */
abstract class Has extends Relationship
{
    /**
     * Get the key for the corresponding relationship

     * @return string          The corresponding relationship key
     */
    protected function guessKey()
    {
        $column = "";
        $class = $this->class;
        $obj = new $class();
        $reflection = new ReflectionClass($this->sourceModel);
        $column = TextTransforms::camelCaseToSnakeCase($reflection->getShortName());
        $column .= "_".$obj->getPrimaryKey();

        return strtoupper($column);
    }
}
