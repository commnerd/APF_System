<?php

namespace System\Components\Relationships;

use System\Components\Model;

class BelongsTo extends Belongs
{
	/**
     * Fetch the related object
     *
     * @return Model       The related model
     */
    public function fetch()
    {
        $key = $this->getKey();
        $class = $this->class;
        $obj = new $class();

        $result = $class::where($obj->getPrimaryKey(), $this->sourceModel->{$key})->get(true);
        if(empty($result)) {
            return null;
        }
        return array_pop($result);
    }
}
