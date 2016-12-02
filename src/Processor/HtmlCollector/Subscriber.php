<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Processor\HtmlCollector;

use Slince\Event\SubscriberInterface;
use Slince\Spider\CollectedUrlEvent;
use Slince\Spider\EventStore;
use Slince\Spider\FilterUrlEvent;
use Slince\Spider\Url;

class Subscriber implements SubscriberInterface
{
    /**
     * @var HtmlCollector
     */
    protected $htmlCollector;

    public function __construct(HtmlCollector $htmlCollector)
    {
        $this->htmlCollector = $htmlCollector;
    }

    public function getEvents()
    {
        return [
            EventStore::FILTER_URL => 'onFilterUrl',
            EventStore::COLLECTED_URL => 'onCollectedUrl',
        ];
    }

    /**
     * 过滤链接事件
     * @param FilterUrlEvent $event
     */
    public function onFilterUrl(FilterUrlEvent $event)
    {
        $url = $event->getUrl();
        $allowHosts = $this->htmlCollector->getAllowHosts();
        if ($allowHosts) {
            $allowHostsPattern = $this->makeAllowHostsPattern($allowHosts);
            if (!preg_match($url->getHost(), $allowHostsPattern)) {
                $event->skipThis(); //如果当前链接host不符合允许的host则跳过
            }
        }
        //如果是重复页面正则并且已经下载过则不再下载
        if (!$this->checkPageUrlPatterns($url)) {
            $event->skipThis();
        }
    }

    /**
     * 采集完毕
     * @param CollectedUrlEvent $event
     */
    public function onCollectedUrl(CollectedUrlEvent $event)
    {
        $asset = $event->getAsset();
        $this->htmlCollector->getFilesystem()->dumpFile($this->htmlCollector->generateFileName($asset), $asset->getContent());
    }


    /**
     * 生成host验证规则
     * @param $allowHosts
     * @return string
     */
    protected function makeAllowHostsPattern($allowHosts)
    {
        return '#(' . implode('|', $allowHosts) . ')#';
    }

    /**
     * 检查是否符合页面正则
     * @param Url $url
     * @return bool
     */
    protected function checkPageUrlPatterns(Url $url)
    {
        $result = true;
        foreach ($this->htmlCollector->getPageUrlPatterns() as $urlPattern) {
            if (preg_match($urlPattern, $url->getUrlString())) {
                //设置模式
                $url->setParameter('pageUrlPattern', $urlPattern);
                if ($this->htmlCollector->getPageUrlDownloadTime($urlPattern)   ) {
                    $result = false;
                    break;
                }
            }
        }
        return $result;
    }
}