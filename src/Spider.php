<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Slince\Event\Dispatcher;
use Slince\Spider\Asset\Asset;
use Slince\Spider\Event\FilterUrlEvent;
use Slince\Spider\Event\CollectUrlEvent;
use Slince\Spider\Event\CollectedUrlEvent;

class Spider
{
    /**
     * 入口链接
     * @var string
     */
    protected $rawEntranceUrl;

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
     * 已经下载的资源
     * @var Asset[]
     */
    protected $assets = [];

    /**
     * 垃圾链接规则
     * @var string
     */
    protected static $junkUrlPattern = '/^(?:#|mailto|javascript):/';

    public function __construct()
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
     * @param $blackUrlPatterns
     */
    public function appendBlackUrlPatterns($blackUrlPatterns)
    {
        $this->blackUrlPatterns = array_merge($this->blackUrlPatterns, $blackUrlPatterns);
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
     * @param $whiteUrlPatterns
     */
    public function appendWhiteUrlPatterns($whiteUrlPatterns)
    {
        $this->whiteUrlPatterns = array_merge($this->whiteUrlPatterns, $whiteUrlPatterns);
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
     * 下载资源
     * @param Url $url
     * @return Asset
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
        if ($this->checkUrlPatterns($url->getRawUrl(), $this->whiteUrlPatterns)) {
            return true;
        }
        //如果符合黑名单规则直接据掉
        if ($this->checkUrlPatterns($url->getRawUrl(), $this->blackUrlPatterns)) {
            return false;
        }
        $filterUrlEvent = new FilterUrlEvent($url, $this);
        $this->dispatcher->dispatch(EventStore::FILTER_URL, new FilterUrlEvent($url, $this));
        return !$filterUrlEvent->isSkipped();
    }

    /**
     * 检查正则
     * @param $url
     * @param array $patterns
     * @return bool
     */
    protected function checkUrlPatterns($url, array $patterns)
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
            $this->dispatcher->dispatch(EventStore::COLLECT_URL, new CollectUrlEvent($url, $this));
            $asset = $this->downloader->download($url);
            $this->dispatcher->dispatch(EventStore::COLLECTED_URL, new CollectedUrlEvent($url, $asset, $this));
            $this->assets[] = $asset;
            TraceReport::report($url);
            if (!$asset->isBinary()) {
                foreach ($asset->getAssetUrls() as $url) {
                    $this->processUrl(Url::createFromUrl($url));
                }
            }
        }
    }

    /**
     * 开始出发
     * @param $url
     */
    public function run($url)
    {
        $this->processUrl(Url::createFromUrl($url));
    }
}
