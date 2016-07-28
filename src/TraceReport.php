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
        $storage = self::getHostStorage($url->getHost());
        $storage->attach($url);
    }

    /**
     * @param Url $url
     * @return bool
     */
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