<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

class Repository
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var Spider
     */
    protected $spider;

    protected static $junkUrlPattern = '/^\s*(?:#|mailto|javascript)/';

    function filterUrl(Url $url)
    {
        //已经下载的链接不再处理
        $pass = !TraceReport::isVisited($url);
        //不在白名单里的链接要进行合法检查
        if (!in_array($url->getRawUrl(), $this->spider->getWhitelistUrls())) {
            if (in_array($url->getRawUrl(), $this->spider->getBlacklistUrls()) ||
                (preg_match($this->junkUrlPattern, $url->getRawUrl()))
            ) {
                $pass = false;
            }
        }
        return $pass;
    }
}