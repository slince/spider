<?php
namespace Slince\Spider\Asset;

class Text extends Asset
{
    /**
     * 支持的mime type
     * @var array
     */
    static $supportedMimeTypes = ['text/plain'];

    /**
     * 获取所有资源链接
     * @return array
     */
    function getAssetUrls()
    {
        return [];
    }
}