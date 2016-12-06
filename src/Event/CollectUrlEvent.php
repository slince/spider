<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Event;

use Slince\Event\Event;
use Slince\Spider\EventStore;
use Slince\Spider\Uri;

class CollectUrlEvent extends Event
{
    /**
     * 事件名称
     * @var string
     */
    const NAME = EventStore::COLLECT_URL;

    /**
     * 当前url
     * @var Uri
     */
    protected $uri;

    public function __construct(Uri $uri, $subject, array $arguments = [])
    {
        $this->url = $uri;
        parent::__construct(static::NAME, $subject, $arguments);
    }

    /**
     * 获取当前url
     * @return Uri
     */
    public function getUrl()
    {
        return $this->url;
    }
}
