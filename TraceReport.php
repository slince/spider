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
     * @var array
     */
    protected static $reports;

    /**
     * 记录爬虫经过的链接
     * @param Url $url
     */
    static function report(Url $url)
    {
        $storage = self::getHostStorage($url->getHost());
        $storage->attach($url);
    }

    static function isVisited(Url $url)
    {
        return self::getHostStorage($url->getHost())->contains($url);
    }

    /**
     * @param string $host
     * @return SplObjectStorage
     */
    protected static function getHostStorage($host)
    {
        if (empty(self::$reports[$host])){
            self::$reports[$host] = new SplObjectStorage();
        }
        return self::$reports[$host];
    }
}