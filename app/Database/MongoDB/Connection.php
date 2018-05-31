<?php

namespace Kinko\Database\MongoDB;

use Closure;
use MongoDB\Client;
use Kinko\Database\NonRelationalConnection;
use Kinko\Database\MongoDB\Query\Builder as QueryBuilder;
use Kinko\Database\MongoDB\Query\Grammar as QueryGrammar;
use Kinko\Database\MongoDB\Schema\Builder as SchemaBuilder;
use Kinko\Database\MongoDB\Schema\Grammar as SchemaGrammar;
use Kinko\Database\MongoDB\Query\Processor as PostProcessor;

class Connection extends NonRelationalConnection
{
    protected $client;

    protected $database;

    protected $schemaBuilder = null;

    protected $schemaGrammar = null;

    protected $queryGrammar = null;

    protected $postProcessor = null;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->client = $this->createClient($config);
        $this->database = $this->client->selectDatabase($config['database']);
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getSchemaBuilder()
    {
        if (is_null($this->schemaBuilder)) {
            $this->schemaBuilder = new SchemaBuilder($this);
        }

        return $this->schemaBuilder;
    }

    public function getSchemaGrammar()
    {
        if (is_null($this->schemaGrammar)) {
            $this->schemaGrammar = new SchemaGrammar();
        }

        return $this->schemaGrammar;
    }

    public function getQueryGrammar()
    {
        if (is_null($this->queryGrammar)) {
            $this->queryGrammar = new QueryGrammar();
        }

        return $this->queryGrammar;
    }

    public function getPostProcessor()
    {
        if (is_null($this->postProcessor)) {
            $this->postProcessor = new PostProcessor();
        }

        return $this->postProcessor;
    }

    public function collection($collection)
    {
        return new QueryBuilder($this, $collection);
    }

    public function raw($value)
    {
        //
    }

    public function selectOne($query, $bindings = [])
    {
        //
    }

    public function select($query, $bindings = [])
    {
        //
    }

    public function insert($query, $bindings = [])
    {
        //
    }

    public function update($query, $bindings = [])
    {
        //
    }

    public function delete($query, $bindings = [])
    {
        //
    }

    public function statement($query, $bindings = [])
    {
        //
    }

    public function affectingStatement($query, $bindings = [])
    {
        //
    }

    public function unprepared($query)
    {
        //
    }

    public function prepareBindings(array $bindings)
    {
        //
    }

    public function transaction(Closure $callback, $attempts = 1)
    {
        //
    }

    public function beginTransaction()
    {
        //
    }

    public function commit()
    {
        //
    }

    public function rollBack()
    {
        //
    }

    public function transactionLevel()
    {
        //
    }

    public function pretend(Closure $callback)
    {
        //
    }

    protected function createClient($config)
    {
        $driverOptions = [];
        if (isset($config['driver_options']) && is_array($config['driver_options'])) {
            $driverOptions = $config['driver_options'];
        }

        $uriOptions = [];
        if (!empty($config['username'])) {
            $uriOptions['username'] = $config['username'];
        }
        if (!empty($config['password'])) {
            $uriOptions['password'] = $config['password'];
        }

        if (!empty($config['dsn'])) {
            $dsn = $config['dsn'];
        } else {
            $dsn = $this->buildDsn($config);
        }

        return new Client($dsn, $uriOptions, $driverOptions);
    }

    protected function buildDsn(array $config)
    {
        // Treat host option as array of hosts
        $hosts = is_array($config['host']) ? $config['host'] : [$config['host']];

        foreach ($hosts as &$host) {
            // Check if we need to add a port to the host
            if (strpos($host, ':') === false && !empty($config['port'])) {
                $host = $host . ':' . $config['port'];
            }
        }

        // Check if we want to authenticate against a specific database.
        $auth_database = isset($config['options']) && !empty($config['options']['database']) ? $config['options']['database'] : null;

        return 'mongodb://' . implode(',', $hosts) . ($auth_database ? '/' . $auth_database : '');
    }
}
