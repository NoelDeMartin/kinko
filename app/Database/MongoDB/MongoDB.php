<?php

namespace Kinko\Database\MongoDB;

use Exception;
use MongoDB\BSON\ObjectId;
use MongoDB\Model\BSONArray;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Carbon;
use MongoDB\Model\BSONDocument;

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

    public function convert($value)
    {
        if ($value instanceof ObjectId) {
            return $this->convertKey($value);
        } elseif ($value instanceof UTCDateTime) {
            return $this->convertDate($value);
        } elseif ($value instanceof BSONDocument) {
            return $this->convertDocument($value);
        } elseif ($value instanceof BSONArray) {
            return $this->convertArray($value);
        } else {
            return $value;
        }
    }

    public function convertKey($value)
    {
        return (string) $value;
    }

    public function convertDate($value)
    {
        if ($value instanceof UTCDateTime) {
            $value = Carbon::createFromTimestamp($value->toDateTime()->getTimestamp());
        }

        return $value;
    }

    public function convertDocument($document)
    {
        $document = (array) $document;

        foreach ($document as $key => $value) {
            $document[$key] = $this->convert($value);
        }

        return $document;
    }

    public function convertArray($array)
    {
        $array = (array) $array;

        foreach ($array as $key => $item) {
            $array[$key] = $this->convert($item);
        }

        return $array;
    }
}
