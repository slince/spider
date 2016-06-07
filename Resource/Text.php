<?php
namespace Slince\Spider\Resource;

class Text extends Resource
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
    function getResourceUrls()
    {
        return [];
    }
}