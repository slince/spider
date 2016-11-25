<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Handler;

use Slince\Event\Event;
use Slince\Spider\EventStore;
use Cake\Database\Connection;

class DatabaseHandler extends AbstractHandler
{
    /**
     * Name
     * @var string
     */
    const NAME = 'database';

    /**
     * database table
     * @var string
     */
    const RESOURCE_TABLE = 'assets';
    /**
     * @var Connection
     */
    protected $connection;

    function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    function getEvents()
    {
        return [
            EventStore::CAPTURED_URL => 'process'
        ];
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param Connection $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Event $event
     */
    function process(Event $event)
    {
        $asset = $event->getArgument('asset');
        $data = [
            'url' => $asset->getUrl(),
            'content_type' => $asset->getContentType(),
            'size' => $asset->getContent()->getSize(),
            'content' => 1233,
            'create_time' => time(),
            'last_visit_time' => time()
        ];
        print_r($data);exit;
        $this->connection->insert(static::RESOURCE_TABLE, $data);
    }
}