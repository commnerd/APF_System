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
        $result = $class::where($foreignKey, $this->sourceModel->getKey())->get();
        if(empty($result)) {
            return null;
        }
        if(is_array($result)) {
            return array_pop($result);
        }

        return $result;
    }
}
