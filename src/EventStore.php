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
    const FILTER_URL = 'filterUri';

    /**
     * 采集url内容事件
     * @var string
     */
    const COLLECT_URL = 'collectUri';

    /**
     * 采集url页面资源事件
     * @var string
     */
    const COLLECT_ASSET_URL = 'collectAssetUri';

    /**
     * 采集url页面资源事件
     * @var string
     */
    const COLLECTED_ASSET_URL = 'collectAssetUri';

    /**
     * 采集完毕url内容事件
     * @var string
     */
    const COLLECTED_URL= 'collectedUri';

    /**
     * 链接下载失败
     * @var string
     */
    const DOWNLOAD_URL_ERROR= 'downloadUriError';

}
