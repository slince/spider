<?php
namespace Slince\Spider\Resource;

class Image extends Resource
{
    /**
     * 支持的mime type
     * @var array
     */
    static $supportedMimeTypes = [
        'image/jpg', 'image/jpeg', 'image/gif', 'image/bmp', 'image/ief',
        'image/svg+xml', 'image/x-icon',
    ];

    function isBinary()
    {
        return true;
    }
}