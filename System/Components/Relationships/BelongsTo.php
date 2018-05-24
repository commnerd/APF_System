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

        return $class::where($key, $this->sourceModel->getKey())->get();
    }
}
