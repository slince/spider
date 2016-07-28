<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Slince\Event\Dispatcher;
use Slince\Event\Event;
use Slince\Spider\Handler\HandlerInterface;

class Spider
{
    /**
     * 入口链接
     * @var string
     */
    protected $rawEntranceUrl;

    /**
     * 要抓取的链接正则
     * @var array
     */
    protected $urlPatterns = [];

    /**
     * 黑名单链接规则
     * @var array
     */
    protected $blackUrlPatterns = [];

    /**
     * 白名单链接
     * @var array
     */
    protected $whiteUrlPatterns = [];

    /**
     * @var Downloader
     */
    protected $downloader;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * 垃圾链接规则
     * @var string
     */
    protected static $junkUrlPattern = '/^\s*(?:#|mailto|javascript)/';

    function __construct()
    {
        $this->downloader = new Downloader();
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @param array $blackUrlPatterns
     */
    public function setBlackUrlPatterns($blackUrlPatterns)
    {
        $this->blackUrlPatterns = $blackUrlPatterns;
    }

    /**
     * @return array
     */
    public function getBlackUrlPatterns()
    {
        return $this->blackUrlPatterns;
    }

    /**
     * @param array $whiteUrlPatterns
     */
    public function setWhiteUrlPatterns($whiteUrlPatterns)
    {
        $this->whiteUrlPatterns = $whiteUrlPatterns;
    }

    /**
     * @return array
     */
    public function getWhiteUrlPatterns()
    {
        return $this->whiteUrlPatterns;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * 添加处理器
     * @param HandlerInterface $handler
     */
    function pushHandler(HandlerInterface $handler)
    {
        $this->dispatcher->addSubscriber($handler);
    }

    /**
     * 下载资源
     * @param Url $url
     * @return Resource
     */
    protected function download(Url $url)
    {
        return $this->downloader->download($url);
    }

    /**
     * 过滤链接
     * @param Url $url
     * @return bool
     */
    protected function filterUrl(Url $url)
    {
        //junk url或者已经访问的链接不再处理
        if (preg_match(self::$junkUrlPattern, $url->getRawUrl()) || TraceReport::isVisited($url)) {
            return false;
        }
        //如果是白名单规则一定通过
        if ($this->checkedUrlPatterns($url->getRawUrl(), $this->whiteUrlPatterns)) {
            return true;
        }
        //如果符合黑名单规则直接据掉
        if ($this->checkedUrlPatterns($url->getRawUrl(), $this->blackUrlPatterns)) {
            return false;
        }
        //默认通过
        return true;
    }

    /**
     * 检查正则
     * @param $url
     * @param array $patterns
     * @return bool
     */
    protected function checkedUrlPatterns($url, array $patterns)
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 处理链接资源
     * @param Url $url
     */
    protected function processUrl(Url $url)
    {
        if ($this->filterUrl($url)) {
            $this->dispatcher->dispatch(EventStore::CAPTURE_URL, new Event(EventStore::CAPTURED_URL, $this, [
                'url' => $url
            ]));
            $resource = $this->downloader->download($url);
            $this->dispatcher->dispatch(EventStore::CAPTURED_URL, new Event(EventStore::CAPTURED_URL, $this, [
                'url' => $url,
                'resource' => $resource
            ]));
            if (!$resource->isBinary()) {
                foreach ($resource->getResourceUrls() as $url) {
                    $this->processUrl(Url::createFromUrl($url));
                }
            }
        }
    }

    /**
     * 开始出发
     * @param $url
     */
    function run($url)
    {
        $this->processUrl(Url::createFromUrl($url));
    }
}