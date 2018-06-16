<?php

namespace Kinko\GraphQL;

class ApplicationType
{
    protected $fields = [];

    public function addField($name, $type, $required = false)
    {
        $this->fields[$name] = [
            'type' => $type,
            'required' => $required,
        ];
    }

    public function toArray()
    {
        return $this->fields;
    }
}
