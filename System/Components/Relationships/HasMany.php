<?php

namespace System\Components\Relationships;

use System\Components\Model;

class HasMany extends Has
{
    /**
     * Fetch the related object
     *
     * @return Model       The related model
     */
    public function fetch()
    {
        $foreignKey = $this->getKey();
        $class = $this->class;
        $results = $class::where($foreignKey, $this->sourceModel->getKey())->get();
        if($results instanceof Model) {
            return array($results);
        }
        return $results;
    }
}
