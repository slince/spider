<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Event;

use Slince\Event\Event;
use Slince\Spider\EventStore;
use Slince\Spider\Uri;

class FilterUriEvent extends Event
{
    /**
     * 事件名称
     * @var string
     */
    const NAME = EventStore::FILTER_URI;

    /**
     * 当前url
     * @var Uri
     */
    protected $uri;

    /**
     * 是否跳过该项
     * @var boolean
     */
    protected $isSkipped;

    public function __construct(Uri $uri, $subject, array $arguments = [])
    {
        $this->url = $uri;
        parent::__construct(static::NAME, $subject, $arguments);
    }

    public function skipThis()
    {
        $this->isSkipped = true;
    }

    /**
     * 是否需要跳过该url
     * @return bool
     */
    public function isSkipped()
    {
        return $this->isSkipped;
    }

    /**
     * 获取当前url
     * @return Uri
     */
    public function getUri()
    {
        return $this->url;
    }
}
