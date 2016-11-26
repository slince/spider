<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Slince\Event\Event;
use Slince\Spider\EventStore;

class FilterUrlEvent extends Event
{
    /**
     * 事件名称
     * @var string
     */
    const NAME = EventStore::FILTER_URL;

    /**
     * 当前url
     * @var Url
     */
    protected $url;

    /**
     * 是否跳过该项
     * @var boolean
     */
    protected $isSkipped;

    public function __construct(Url $url, $subject, array $arguments = [])
    {
        $this->url = $url;
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
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }
}