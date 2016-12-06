<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Processor\HtmlCollector;

use Slince\Event\SubscriberInterface;
use Slince\Spider\EventStore;
use Slince\Spider\Event\CollectedUrlEvent;
use Slince\Spider\Event\FilterUrlEvent;

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
            EventStore::COLLECTED_URL => 'onCollectedUrl'
        ];
    }

    /**
     * 过滤链接事件
     * @param FilterUrlEvent $event
     */
    public function onFilterUrl(FilterUrlEvent $event)
    {
        $url = $event->getUrl();
        if (!$this->htmlCollector->checkUrlEnabled($url)) {
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
        $this->htmlCollector->saveAsset($asset);
    }
}