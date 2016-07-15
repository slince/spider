<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Handler;

use Slince\Spider\Resource\ResourceInterface;

class FileHandler extends AbstractHandler
{
    protected $savePath;

    function __construct($savePath = './')
    {
        $this->savePath = $savePath;
    }

    function handle(ResourceInterface $resource)
    {
        @file_put_contents($this->generateFilename($resource), $resource->getContent());
    }

    protected function generateFilename(ResourceInterface $resource)
    {
        return rtrim($this->savePath, '\\/') . '/' . urlencode($resource->getUrl());
    }
}