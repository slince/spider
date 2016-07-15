<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Resource;

interface ResourceInterface
{
    function getUrl();

    function getContent();

    function getContentType();
}