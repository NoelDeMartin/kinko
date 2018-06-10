<?php

namespace Kinko\Database;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Database\ConnectionResolverInterface;

class DatabaseManager implements ConnectionResolverInterface
{
    protected $app;

    protected $connections = [];

    protected $resolvers = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function connection($name = null)
    {
        list($database, $type) = $this->parseConnectionName($name);

        $name = $name ? : $database;

        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($database);
        }

        return $this->connections[$name];
    }

    public function resolve($name, callable $resolver)
    {
        $this->resolvers[$name] = $resolver;
    }

    public function getDefaultConnection()
    {
        return $this->app['config']['database.default'];
    }

    public function setDefaultConnection($name)
    {
        $this->app['config']['database.default'] = $name;
    }

    protected function parseConnectionName($name)
    {
        $name = $name ? : $this->getDefaultConnection();

        return Str::endsWith($name, ['::read', '::write'])
            ? explode('::', $name, 2) : [$name, null];
    }

    protected function makeConnection($name)
    {
        $config = $this->configuration($name);

        // First we will check by the connection name to see if a resolver has been
        // registered specifically for that connection. If it has we will call the
        // Closure and pass it the config allowing it to resolve the connection.
        if (isset($this->resolvers[$name])) {
            return call_user_func($this->resolvers[$name], $config, $name);
        }

        // Next we will check to see if a resolver has been registered for a driver
        // and will call the Closure if so, which allows us to have a more generic
        // resolver for the drivers themselves which applies to all connections.
        if (isset($this->resolvers[$driver = $config['driver']])) {
            return call_user_func($this->resolvers[$driver], $config, $name);
        }

        // If not resolver has been registered, we'll throw an exception and bail.
        throw new InvalidArgumentException("[{$name}] Database resolver not configured.");
    }

    protected function configuration($name)
    {
        $name = $name ?: $this->getDefaultConnection();

        // To get the database connection configuration, we will just pull each of the
        // connection configurations and get the configurations for the given name.
        // If the configuration doesn't exist, we'll throw an exception and bail.
        $connections = $this->app['config']['database.connections'];

        if (is_null($config = Arr::get($connections, $name))) {
            throw new InvalidArgumentException("Database [{$name}] not configured.");
        }

        return $config;
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}
