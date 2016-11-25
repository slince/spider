<?php
namespace Slince\Spider\Asset;

class Script extends Asset
{
    /**
     * 支持的mime type
     * @var array
     */
    static $supportedMimeTypes = ['text/javascript', 'application/x-javascript'];

    /**
     * 获取所有资源链接
     * @return array
     */
    function getAssetUrls()
    {
        return [];
    }
}