<?php

namespace Kinko\Database\MongoDB;

use Exception;
use MongoDB\BSON\ObjectId;

class MongoDB
{
    public function key($value, $throwException = false)
    {
        if (!is_null($value) && !($value instanceof ObjectId)) {
            try {
                $value = new ObjectId($value);
            } catch (Exception $e) {
                if ($throwException) {
                    throw $e;
                }
            }
        }

        return $value;
    }
}