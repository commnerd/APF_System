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
        $obj->{$this->column} = $this->sourceModel->getKey();

        $result = $class::where($obj->getPrimaryKey(), $this->sourceModel->{$key})->get();
        if(empty($result)) {
            return $obj;
        }
        foreach($result as $key => $obj) {
           $obj->{$this->column} = $this->sourceModel->getKey();
           $results[$key] = $obj;
        }
        return array_pop($result);
    }
}
