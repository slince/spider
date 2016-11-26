<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use SplObjectStorage;

class TraceReport
{
    /**
     * @var SplObjectStorage[]
     */
    protected static $reports;

    /**
     * 记录爬虫访问过的链接
     * @param Url $url
     */
    static function report(Url $url)
    {
        $storage = static::getHostStorage($url->getHost());
        $storage->attach($url);
    }

    /**
     * @param Url $url
     * @return bool
     */
    static function isVisited(Url $url)
    {
        return static::getHostStorage($url->getHost())->contains($url);
    }

    /**
     * @param string $host
     * @return SplObjectStorage
     */
    protected static function getHostStorage($host)
    {
        if (!isset(static::$reports[$host])) {
            static::$reports[$host] = new SplObjectStorage();
        }
        return static::$reports[$host];
    }
}
