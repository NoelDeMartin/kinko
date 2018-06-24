<?php

namespace Kinko\Database\MongoDB;

use Exception;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Carbon;

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

    public function date($value, $throwException = false)
    {
        if (!is_null($value) && !($value instanceof UTCDateTime)) {
            try {
                $value = new UTCDateTime($value);
            } catch (Exception $e) {
                if ($throwException) {
                    throw $e;
                }
            }
        }

        return $value;
    }

    public function convertDate($value)
    {
        if ($value instanceof UTCDateTime) {
            $value = Carbon::createFromTimestamp($value->toDateTime()->getTimestamp());
        }

        return $value;
    }
}
