<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Slince\Event\Dispatcher;
use Slince\Spider\Asset\Asset;
use Slince\Spider\Event\CollectAssetUrlEvent;
use Slince\Spider\Event\CollectedAssetUrlEvent;
use Slince\Spider\Event\DownloadUrlErrorEvent;
use Slince\Spider\Event\FilterUrlEvent;
use Slince\Spider\Event\CollectUrlEvent;
use Slince\Spider\Event\CollectedUrlEvent;
use Slince\Spider\Exception\RuntimeException;

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
     * @param Uri $uri
     * @return Asset
     */
    protected function download(Uri $uri)
    {
        return $this->downloader->download($uri);
    }

    /**
     * 过滤链接
     * @param Uri $uri
     * @return bool
     */
    protected function filterUrl(Uri $uri)
    {
        //junk url或者已经访问的链接不再处理
        if (preg_match(self::$junkUrlPattern, $uri->getRawUrl()) || TraceReport::instance()->isVisited($uri)) {
            return false;
        }
        //如果是白名单规则一定通过
        if ($this->checkUrlPatterns($uri->getRawUrl(), $this->whiteUrlPatterns)) {
            return true;
        }
        //如果符合黑名单规则直接据掉
        if ($this->checkUrlPatterns($uri->getRawUrl(), $this->blackUrlPatterns)) {
            return false;
        }
        $filterUrlEvent = new FilterUrlEvent($uri, $this);
        $this->dispatcher->dispatch(EventStore::FILTER_URL, $filterUrlEvent);
        return !$filterUrlEvent->isSkipped();
    }

    /**
     * 检查正则
     * @param $uri
     * @param array $patterns
     * @return bool
     */
    protected function checkUrlPatterns($uri, array $patterns)
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 处理链接资源
     * @param Uri $uri
     * @return boolean
     */
    protected function processUrl(Uri $uri)
    {
        if ($this->filterUrl($uri)) {
            $this->dispatcher->dispatch(EventStore::COLLECT_URL, new CollectUrlEvent($uri, $this));
            TraceReport::instance()->report($uri);
            try {
                $asset = $this->downloader->download($uri);
            } catch (RuntimeException $exception) {
                $this->dispatcher->dispatch(EventStore::DOWNLOAD_URL_ERROR, new DownloadUrlErrorEvent($uri, $this));
                return false;
            }
            //记录已采集的链接
            $this->assets[] = $asset;
            //处理该链接下的资源
            $enabledProcessChildrenUrl = !$asset->isBinary() && $asset->getContent();
            if ($enabledProcessChildrenUrl) {
                $this->dispatcher->dispatch(EventStore::COLLECT_ASSET_URL, new CollectAssetUrlEvent($uri, $asset, $this));
                foreach ($asset->getAssetUrls() as $uri) {
                    $this->processUrl($uri);
                }
                $this->dispatcher->dispatch(EventStore::COLLECTED_ASSET_URL, new CollectedAssetUrlEvent($uri, $asset, $this));
            }
            $this->dispatcher->dispatch(EventStore::COLLECTED_URL, new CollectedUrlEvent($uri, $asset, $this));
            //采集周期结束之后处理其它链接
            if ($enabledProcessChildrenUrl) {
                foreach ($asset->getPageUrls() as $uri) {
                    $this->processUrl($uri);
                }
            }
        }
        return true;
    }

    /**
     * 开始出发
     * @param $uri
     */
    public function run($uri)
    {
        $this->processUrl(new Uri($uri));
    }
}
