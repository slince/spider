<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Slince\Spider\Resource\Resource;

class Downloader
{

    /**
     * @param Url $url
     * @return Resource
     */
    function download(Url $url)
    {
        return new Resource(@file_get_contents($url));
    }
}