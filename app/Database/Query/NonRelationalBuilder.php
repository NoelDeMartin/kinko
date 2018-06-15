<?php

namespace Kinko\Database\Query;

use Illuminate\Database\Query\Builder;

abstract class NonRelationalBuilder extends Builder
{
    /**
     * Execute an accumulation function on the database.
     *
     * @param  string  $field
     * @param  string  $operation
     * @return mixed
     */
    abstract public function accumulate($field, $operation);

    /**
     * Retrieve the minimum value of a given field.
     *
     * @param  string  $field
     * @return mixed
     */
    public function min($field)
    {
        return $this->accumulate($field, 'min');
    }

    /**
     * Retrieve the maximum value of a given field.
     *
     * @param  string  $field
     * @return mixed
     */
    public function max($field)
    {
        return $this->accumulate($field, 'max');
    }
}
