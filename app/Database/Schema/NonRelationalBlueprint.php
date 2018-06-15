<?php

namespace Kinko\Database\Schema;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;

abstract class NonRelationalBlueprint extends Blueprint
{
    protected $collection;

    protected $fields = [];

    public function __construct($collection)
    {
        parent::__construct($collection);
        $this->collection = $collection;
    }

    /**
     * Execute the blueprint against the database.
     *
     * @param  \Kinko\Database\NonRelationalConnection  $connection
     * @return void
     */
    public function apply($connection)
    {
        $this->connection = $connection;
        $this->createCollection();

        foreach ($this->fields as $name => $field) {
            foreach ($field->getAttributes() as $index => $parameters) {
                $this->createIndex($name, $index, $parameters);
            }
        }
    }

    /**
     * Add a new field to the blueprint.
     *
     * @param  string  $name
     * @return \Illuminate\Support\Fluent
     */
    public function field($name)
    {
        $field = new Fluent();
        $this->fields[$name] = $field;

        return $field;
    }

    abstract protected function createCollection();

    abstract protected function createIndex($field, $index, $parameters);
}
