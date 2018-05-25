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

        $result = $class::where($key, $this->sourceModel->getKey())->get();
        if(is_array($results)) {
            return array_pop($result);
        }

        return $result;
    }
}
