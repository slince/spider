<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Processor\HtmlCollector;

use Slince\Event\SubscriberInterface;
use Slince\Spider\Asset\Asset;
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

    public function getEvents()
    {
        return [
            EventStore::FILTER_URL => 'onFilterUrl'
        ];
    }

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

    public function onCollectedUrl(CollectedUrlEvent $event)
    {
        $url = $event->getUrl();
        $asset = $event->getAsset();

    }


    protected function makeAllowHostsPattern($allowHosts)
    {
        return '#(' . implode('|', $allowHosts) . ')#';
    }

    protected function checkPageUrlPatterns(Url $url)
    {
        $result = true;
        foreach ($this->htmlCollector->getPageUrlPatterns() as $urlPattern) {
            if (preg_match($urlPattern, $url->getUrlString())) {
                if ($this->htmlCollector->getPageUrlDownloadTime($urlPattern)   ) {
                    $result = false;
                    break;
                }
            }
        }
        return $result;
    }
}