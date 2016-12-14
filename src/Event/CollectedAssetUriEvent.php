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

class CollectedAssetUriEvent extends Event
{
    /**
     * 事件名称
     * @var string
     */
    const NAME = EventStore::COLLECTED_ASSET_URI;

    /**
     * 当前url
     * @var Uri
     */
    protected $uri;

    /**
     * 所属资源
     * @var AssetInterface
     */
    protected $ownerAsset;

    public function __construct(Uri $uri, AssetInterface $ownerAsset, $subject, array $arguments = [])
    {
        $this->url = $uri;
        $this->ownerAsset = $ownerAsset;
        parent::__construct(static::NAME, $subject, $arguments);
    }

    /**
     * 获取当前url
     * @return Uri
     */
    public function getUri()
    {
        return $this->url;
    }

    /**
     * 获取资源
     * @return AssetInterface
     */
    public function getOwnerAsset()
    {
        return $this->ownerAsset;
    }
}
