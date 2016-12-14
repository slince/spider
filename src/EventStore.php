<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

class EventStore
{
    /**
     * 过滤url事件
     * @var string
     */
    const FILTER_URI = 'filterUri';

    /**
     * 采集url内容事件
     * @var string
     */
    const COLLECT_URI = 'collectUri';

    /**
     * 采集url页面资源事件
     * @var string
     */
    const COLLECT_ASSET_URI = 'collectAssetUri';

    /**
     * 采集url页面资源事件
     * @var string
     */
    const COLLECTED_ASSET_URI = 'collectAssetUri';

    /**
     * 采集完毕url内容事件
     * @var string
     */
    const COLLECTED_URI= 'collectedUri';

    /**
     * 链接下载失败
     * @var string
     */
    const DOWNLOAD_URI_ERROR= 'downloadUriError';

}
