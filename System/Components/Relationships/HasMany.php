<?php

namespace System\Components\Relationships;

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
        $this->query = $class::where($foreignKey, $this->sourceModel->getKey())->select();
        return $this;
    }
}
