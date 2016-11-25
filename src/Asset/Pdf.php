<?php
namespace Slince\Spider\Asset;

class Pdf extends Asset
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
    public function getAssetUrls()
    {
        return [];
    }
}