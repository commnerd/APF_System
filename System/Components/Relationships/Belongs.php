<?php

namespace System\Components\Relationships;

use System\Services\TextTransforms;
use ReflectionClass;

/**
 * Convenience class for categorization
 */
abstract class Belongs extends Relationship
{
    /**
     * Get the key for the corresponding relationship
     *
     * @return string          The corresponding relationship key
     */
    protected function guessKey()
    {
        $column = "";
        $class = $this->class;
        $obj = new $class();
        $reflection = new ReflectionClass($obj);
        $column = TextTransforms::camelCaseToSnakeCase($reflection->getShortName());
        $column .= "_".$this->sourceModel->getPrimaryKey();

        return strtoupper($column);
    }

}
