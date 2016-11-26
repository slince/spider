<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Slince\Event\Event;
use Slince\Spider\Asset\AssetInterface;
use Slince\Spider\EventStore;

class CollectedUrlEvent extends Event
{
    /**
     * 事件名称
     * @var string
     */
    const NAME = EventStore::COLLECTED_URL;

    /**
     * 当前url
     * @var Url
     */
    protected $url;

    /**
     * url对应资源
     * @var AssetInterface
     */
    protected $asset;

    public function __construct(Url $url, AssetInterface $asset, $subject, array $arguments = [])
    {
        $this->url = $url;
        $this->asset = $asset;
        parent::__construct(static::NAME, $subject, $arguments);
    }

    /**
     * 获取当前url
     * @return Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * 获取资源
     * @return AssetInterface
     */
    public function getAsset()
    {
        return $this->asset;
    }
}