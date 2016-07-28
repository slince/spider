<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Handler;

use Slince\Event\Event;
use Slince\Spider\EventStore;
use Slince\Spider\Resource\ResourceInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileHandler extends AbstractHandler
{
    /**
     * @var string
     */
    protected $savePath;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    function __construct($savePath = './')
    {
        $this->savePath = $savePath;
        $this->filesystem = new Filesystem();
    }

    /**
     * 获取events
     * @return array
     */
    function getEvents()
    {
        return [
            EventStore::CAPTURED_URL => 'process'
        ];
    }

    /**
     * @param Event $event
     */
    function process(Event $event)
    {
        $resource = $event->getArgument('resource');
        $filename = $this->generateFilename($resource);
        $this->filesystem->dumpFile($filename, $resource->getContent());
    }

    /**
     * 生成文件名
     * @param ResourceInterface $resource
     * @return string
     */
    protected function generateFilename(ResourceInterface $resource)
    {
        return rtrim($this->savePath, '\\/') . '/' . $resource->getUrl();
    }
}