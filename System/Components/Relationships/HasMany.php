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
        $this->query = $this->class::where($foreignKey, $this->sourceModel->getKey())->get();
        return $this;
    }
}
