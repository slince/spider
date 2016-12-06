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
     * 路径存储
     * @var ArrayObject[]
     */
    protected $reports;

    /**
     * 当前路径实例
     * @var TraceReport
     */
    protected static $instance;

    /**
     * 记录爬虫访问过的链接
     * @param Url $url
     */
    public function report(Url $url)
    {
        $storage = $this->getHostStorage($url->getHost());
        $storage[static::hash($url)] = $url;
    }

    /**
     * @param Url $url
     * @return bool
     */
    public function isVisited(Url $url)
    {
        return isset($this->getHostStorage($url->getHost())[static::hash($url)]);
    }

    /**
     * hash
     * @param Url $url
     * @return string
     */
    protected function hash(Url $url)
    {
        return md5($url->getUrlString());
    }

    /**
     * @param string $host
     * @return ArrayObject
     */
    protected function getHostStorage($host)
    {
        if (!isset($this->reports[$host])) {
            $this->reports[$host] = new ArrayObject();
        }
        return $this->reports[$host];
    }

    /**
     * 获取报告
     * @return TraceReport
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 设置当前路径报告
     * @param TraceReport $instance
     */
    public static function setInstance(TraceReport $instance)
    {
        static::$instance = $instance;
    }
}
