<?php

namespace Kinko\GraphQL;

class ApplicationSchema
{
    protected $types = [];

    public function addType($name, ApplicationType $type)
    {
        $this->types[$name] = $type;
    }

    public function toArray()
    {
        return array_map(function ($type) {
            return $type->toArray();
        }, $this->types);
    }
}
