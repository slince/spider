<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Slince\Event\Dispatcher;
use Slince\Spider\Asset\Asset;
use Slince\Spider\Event\CollectAssetUriEvent;
use Slince\Spider\Event\CollectedAssetUriEvent;
use Slince\Spider\Event\DownloadUriErrorEvent;
use Slince\Spider\Event\FilterUriEvent;
use Slince\Spider\Event\CollectUriEvent;
use Slince\Spider\Event\CollectedUriEvent;
use Slince\Spider\Exception\RuntimeException;

class Spider
{
    /**
     * 入口链接
     * @var string
     */
    protected $rawEntranceUri;

    /**
     * 黑名单链接规则
     * @var array
     */
    protected $blackUriPatterns = [];

    /**
     * 白名单链接
     * @var array
     */
    protected $whiteUriPatterns = [];

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
    protected static $junkUriPattern = '/^(?:#|mailto|javascript):/';

    public function __construct()
    {
        $this->downloader = new Downloader();
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @param array $blackUriPatterns
     */
    public function setBlackUriPatterns($blackUriPatterns)
    {
        $this->blackUriPatterns = $blackUriPatterns;
    }

    /**
     * @param $blackUriPatterns
     */
    public function appendBlackUriPatterns($blackUriPatterns)
    {
        $this->blackUriPatterns = array_merge($this->blackUriPatterns, $blackUriPatterns);
    }

    /**
     * @return array
     */
    public function getBlackUriPatterns()
    {
        return $this->blackUriPatterns;
    }

    /**
     * @param array $whiteUriPatterns
     */
    public function setWhiteUriPatterns($whiteUriPatterns)
    {
        $this->whiteUriPatterns = $whiteUriPatterns;
    }

    /**
     * @param $whiteUriPatterns
     */
    public function appendWhiteUriPatterns($whiteUriPatterns)
    {
        $this->whiteUriPatterns = array_merge($this->whiteUriPatterns, $whiteUriPatterns);
    }

    /**
     * @return array
     */
    public function getWhiteUriPatterns()
    {
        return $this->whiteUriPatterns;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @return Downloader
     */
    public function getDownloader()
    {
        return $this->downloader;
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
    protected function filterUri(Uri $uri)
    {
        //junk url或者已经访问的链接不再处理
        $uriString  = strval($uri);
        if (preg_match(self::$junkUriPattern, $uriString) || TraceReport::instance()->isVisited($uri)) {
            return false;
        }
        //如果是白名单规则一定通过
        if ($this->checkUriPatterns($uriString, $this->whiteUriPatterns)) {
            return true;
        }
        //如果符合黑名单规则直接据掉
        if ($this->checkUriPatterns($uriString, $this->blackUriPatterns)) {
            return false;
        }
        $filterUriEvent = new FilterUriEvent($uri, $this);
        $this->dispatcher->dispatch(EventStore::FILTER_URI, $filterUriEvent);
        return !$filterUriEvent->isSkipped();
    }

    /**
     * 检查正则
     * @param $uri
     * @param array $patterns
     * @return bool
     */
    protected function checkUriPatterns($uri, array $patterns)
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
    protected function processUri(Uri $uri)
    {
        if ($this->filterUri($uri)) {
            $this->dispatcher->dispatch(EventStore::COLLECT_URI, new CollectUriEvent($uri, $this));
            TraceReport::instance()->report($uri);
            try {
                $asset = $this->downloader->download($uri);
            } catch (RuntimeException $exception) {
                $this->dispatcher->dispatch(EventStore::DOWNLOAD_URI_ERROR, new DownloadUriErrorEvent($uri, $this));
                return false;
            }
            //记录已采集的链接
            $this->assets[] = $asset;
            //处理该链接下的资源
            $enabledProcessChildrenUri = !$asset->isBinary() && $asset->getContent();
            if ($enabledProcessChildrenUri) {
                $this->dispatcher->dispatch(EventStore::COLLECT_ASSET_URI, new CollectAssetUriEvent($uri, $asset, $this));
                foreach ($asset->getAssetUris() as $uri) {
                    $this->processUri($uri);
                }
                $this->dispatcher->dispatch(EventStore::COLLECTED_ASSET_URI, new CollectedAssetUriEvent($uri, $asset, $this));
            }
            $this->dispatcher->dispatch(EventStore::COLLECTED_URI, new CollectedUriEvent($uri, $asset, $this));
            //采集周期结束之后处理其它链接
            if ($enabledProcessChildrenUri) {
                foreach ($asset->getPageUris() as $uri) {
                    $this->processUri($uri);
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
        $this->processUri(new Uri($uri));
    }
}
