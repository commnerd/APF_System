<?php

namespace System\Components\Relationships;

use System\Components\Model;

class BelongsToMany extends Belongs
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
        $results = $class::where($key, $this->sourceModel->getKey())->get();
        if($results instanceof Model) {
            return array($results);
        }
        return $results;
    }
}
