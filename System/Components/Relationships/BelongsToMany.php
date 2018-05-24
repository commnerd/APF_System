<?php

namespace System\Components\Relationships;

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
        return $class::where($key, $this->sourceModel->getKey())->get();
    }
}
