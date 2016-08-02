<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Slince\Spider\Handler\DatabaseHandler;
use Slince\Spider\Handler\FileHandler;
use Slince\Spider\Exception\InvalidArgumentException;

class Factory
{
    /**
     * Create Handler
     * @param $type
     * @param $config
     * @return DatabaseHandler|FileHandler
     */
    static function createHandler($type, $config)
    {
        switch ($type) {
            case FileHandler::NAME:
                $handler = static::createFileHandler($config);
                break;
            case DatabaseHandler::NAME:
                $handler = static::createDatabaseHandler($config);
                break;
            default:
                throw new InvalidArgumentException(sprintf("Handler [%s] is not supported", $type));
        }
        return $handler;
    }

    /**
     * Create Database Handler
     * @param $config
     * @return DatabaseHandler
     */
    static function createDatabaseHandler($config)
    {
        return new DatabaseHandler(static::createDatabaseConnection($config));
    }

    /**
     * Create File Handler
     * @param $savePath
     * @return FileHandler
     */
    static function createFileHandler($savePath)
    {
        return new FileHandler($savePath);
    }

    /**
     * Create Database Connection
     * @param $config
     * @return Connection
     */
    protected static function createDatabaseConnection($config)
    {
        $driver = new Mysql($config);
        $connection = new Connection([
            'driver' => $driver
        ]);
        return $connection;
    }

}