<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Handler;

use Slince\Event\Event;
use Slince\Spider\EventStore;
use Slince\Spider\Asset\AssetInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileHandler extends AbstractHandler
{
    /**
     * Name
     * @var string
     */
    const NAME = 'file';

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
        $asset = $event->getArgument('asset');
        $filename = $this->generateFilename($asset);
        $this->filesystem->dumpFile($filename, $asset->getContent());
    }

    /**
     * 生成文件名
     * @param AssetInterface $asset
     * @return string
     */
    protected function generateFilename(AssetInterface $asset)
    {
        return rtrim($this->savePath, '\\/') . '/' . $asset->getUrl();
    }
}