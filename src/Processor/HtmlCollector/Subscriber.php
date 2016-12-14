<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Processor\HtmlCollector;

use Slince\Event\SubscriberInterface;
use Slince\Spider\EventStore;
use Slince\Spider\Event\CollectedUriEvent;
use Slince\Spider\Event\FilterUriEvent;

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
            EventStore::FILTER_URI => 'onFilterUri',
            EventStore::COLLECTED_URI => 'onCollectedUri'
        ];
    }

    /**
     * 过滤链接事件
     * @param FilterUriEvent $event
     */
    public function onFilterUri(FilterUriEvent $event)
    {
        $uri = $event->getUri();
        if (!$this->htmlCollector->checkUriEnabled($uri)) {
            $event->skipThis();
        }
    }

    /**
     * 采集完毕
     * @param CollectedUriEvent $event
     */
    public function onCollectedUri(CollectedUriEvent $event)
    {
        $asset = $event->getAsset();
        $this->htmlCollector->saveAsset($asset);
    }
}