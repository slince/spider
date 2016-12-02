<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Event;

use Slince\Event\Event;
use Slince\Spider\EventStore;
use Slince\Spider\Url;

class CollectUrlEvent extends Event
{
    /**
     * 事件名称
     * @var string
     */
    const NAME = EventStore::COLLECT_URL;

    /**
     * 当前url
     * @var Url
     */
    protected $url;

    public function __construct(Url $url, $subject, array $arguments = [])
    {
        $this->url = $url;
        parent::__construct(static::NAME, $subject, $arguments);
    }

    /**
     * 获取当前url
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }
}
