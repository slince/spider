<?php
namespace Slince\Spider\Resource;

class Pdf extends Resource
{
    /**
     * 支持的mime type
     * @var array
     */
    static $supportedMimeTypes = ['application/pdf'];

    /**
     * 获取所有资源链接
     * @return array
     */
    function getResourceUrls()
    {
        return [];
    }
}