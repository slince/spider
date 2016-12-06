<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Event;

use Slince\Event\Event;
use Slince\Spider\Asset\AssetInterface;
use Slince\Spider\EventStore;
use Slince\Spider\Uri;

class CollectedUrlEvent extends Event
{
    /**
     * 事件名称
     * @var string
     */
    const NAME = EventStore::COLLECTED_URL;

    /**
     * 当前url
     * @var Uri
     */
    protected $uri;

    /**
     * url对应资源
     * @var AssetInterface
     */
    protected $asset;

    public function __construct(Uri $uri, AssetInterface $asset, $subject, array $arguments = [])
    {
        $this->url = $uri;
        $this->asset = $asset;
        parent::__construct(static::NAME, $subject, $arguments);
    }

    /**
     * 获取当前url
     * @return Uri
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
