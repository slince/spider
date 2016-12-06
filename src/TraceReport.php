<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use ArrayObject;

class TraceReport
{
    /**
     * @var array
     */
    protected static $reports;

    /**
     * 记录爬虫访问过的链接
     * @param Url $url
     */
    static function report(Url $url)
    {
        $storage = static::getHostStorage($url->getHost());
        $storage[static::hash($url)] = $url;
    }

    /**
     * @param Url $url
     * @return bool
     */
    static function isVisited(Url $url)
    {
        return isset(static::getHostStorage($url->getHost())[static::hash($url)]);
    }

    /**
     * hash
     * @param Url $url
     * @return string
     */
    protected static function hash(Url $url)
    {
        return md5($url->getRawUrl());
    }

    /**
     * @param string $host
     * @return array
     */
    protected static function getHostStorage($host)
    {
        if (!isset(static::$reports[$host])) {
            static::$reports[$host] = new ArrayObject();
        }
        return static::$reports[$host];
    }
}
