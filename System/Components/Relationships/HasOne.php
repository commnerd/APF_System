<?php

namespace System\Components\Relationships;

class HasOne extends Has
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
        return $class::where($foreignKey, $this->sourceModel->getKey())->get();
    }
}
