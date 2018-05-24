<?php

namespace System\Components\Relationships;

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

        $this->query = $class::where($key, $this->sourceModel->getKey())->select();
        return $this;
    }
}
