<?php

use Illuminate\Support\Collection;
use Kinko\Database\Soukai\NonRelationalModel;

if (!function_exists('resource')) {
    /**
     * Convert objects into api resources.
     *
     * @param  mixed  $models
     * @return mixed
     */
    function resource($result)
    {
        if (is_array($result) || $result instanceof Collection) {
            if (count($result) > 0 && $result[0] instanceof NonRelationalModel) {
                $resourceClass = $result[0]->getResourceClass();
                return $resourceClass::collection($result);
            } else {
                return $result;
            }
        } elseif ($result instanceof NonRelationalModel) {
            return $result->resource();
        } else {
            return $result;
        }
    }
}
