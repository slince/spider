<?php
namespace Slince\Spider\Resource;

class Image extends Resource
{
    /**
     * 资源类型
     * @var string
     */
    static $contentType = 'image/jpeg';

    function isBinary()
    {
        return true;
    }
}