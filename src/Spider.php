<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Slince\Event\Dispatcher;
use Slince\Event\Event;
use Slince\Spider\Handler\HandlerInterface;
use Slince\Spider\Resource\Page;

class Spider
{
    /**
     * 过滤url事件
     * @var string
     */
    const EVENT_FILTERED_URL = 'filteredUrl';

    /**
     * 采集url内容事件
     * @var string
     */
    const EVENT_CAPTURE_URL = 'captureUrl';

    /**
     * 采集完毕url内容事件
     * @var string
     */
    const EVENT_CAPTURED_URL= 'capturedUrl';

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
     * 是否只抓取指定链接规则
     * @var bool
     */
    protected $onlyCaptureUrlPatterns = true;

    /**
     * 黑名单链接
     * @var array
     */
    protected $blacklistUrls = [];

    /**
     * 白名单链接
     * @var array
     */
    protected $whitelistUrls = [];

    /**
     * 允许抓取的host，避免站外链接导致
     * 抓取过多页面
     * @var array
     */
    protected $allowedCaptureHosts = [];

    /**
     * 已经下载的链接正则
     * @var array
     */
    protected $downloadedUrlPatterns = [];


    /**
     * @var Downloader
     */
    protected $downloader;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    protected static $junkUrlPattern = '/^\s*(?:#|mailto|javascript)/';

    function __construct()
    {
        $this->downloader = new Downloader();
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @param array $allowedCaptureHosts
     */
    public function setAllowedCaptureHosts($allowedCaptureHosts)
    {
        $this->allowedCaptureHosts = $allowedCaptureHosts;
    }

    /**
     * @return array
     */
    public function getAllowedCaptureHosts()
    {
        return $this->allowedCaptureHosts;
    }


    /**
     * @param array $blacklistUrls
     */
    public function setBlacklistUrls($blacklistUrls)
    {
        $this->blacklistUrls = $blacklistUrls;
    }

    /**
     * @return array
     */
    public function getBlacklistUrls()
    {
        return $this->blacklistUrls;
    }

    /**
     * @param array $whitelistUrls
     */
    public function setWhitelistUrls($whitelistUrls)
    {
        $this->whitelistUrls = $whitelistUrls;
    }

    /**
     * @return array
     */
    public function getWhitelistUrls()
    {
        return $this->whitelistUrls;
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

    protected function filterUrl(Url $url)
    {
        //已经下载的链接不再处理
        $pass = !TraceReport::isVisited($url);
        //不在白名单里的链接要进行合法检查
        if (!in_array($url->getRawUrl(), $this->whitelistUrls)) {
            if (in_array($url->getRawUrl(), $this->blacklistUrls) ||
                (preg_match(self::$junkUrlPattern, $url->getRawUrl()))
            ) {
                $pass = false;
            }
        }
        return $pass;
    }

    /**
     * 处理资源
     * @param Url $url
     */
    protected function processUrl(Url $url)
    {
        if ($this->filterUrl($url)) {
            $resource = $this->downloader->download($url);
            $this->dispatcher->dispatch(self::EVENT_CAPTURED_URL, new Event(self::EVENT_CAPTURED_URL, $this, [
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
    function go($url)
    {
        $this->processUrl(Url::createFromUrl($url));
    }
}