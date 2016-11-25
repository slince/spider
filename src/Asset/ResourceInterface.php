<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Asset;

use Slince\Spider\Url;

interface AssetInterface
{
    /**
     * @return Url
     */
    function getUrl();

    function getContent();

    function getContentType();
}