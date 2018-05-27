<?php

namespace Kinko\Database\Schema;

use Illuminate\Support\Fluent;

abstract class NonRelationalBlueprint
{
    protected $collection;

    protected $connection = null;

    protected $fields = [];

    public function __construct($collection)
    {
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

    /* These should be removed (forced by usage of
     * Illuminate/Database/Migrations/DatabaseMigrationRepository class)
     **/

    public function increments($column)
    {
        //
    }

    public function string($column)
    {
        //
    }

    public function integer($column)
    {
        //
    }
}
