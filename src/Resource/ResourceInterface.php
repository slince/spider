<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Resource;

use Slince\Spider\Url;

interface ResourceInterface
{
    /**
     * @return Url
     */
    function getUrl();

    function getContent();

    function getContentType();
}