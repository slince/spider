<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Processor\HtmlCollector;

use Slince\Spider\Asset\Asset;
use Slince\Spider\Processor\Processor;
use Slince\Spider\Spider;
use Symfony\Component\Filesystem\Filesystem;

class HtmlCollector extends Processor
{
    /**
     * 允许采集的host，防止过度采集
     * @var array
     */
    protected $allowHosts = [];

    /**
     * 重点采集的页面的正则，防止相似页面过多采集
     * example:
     * [
     *     '#http://www.domain.com/articles/\d+.html#' => 'article.html'
     * ]
     * @var array
     */
    protected $pageUrlPatterns = [];

    protected $pageUrlPatternDownloadTimes = [];

    /**
     * 下载模板保存路径
     * @var string
     */
    protected $savePath;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct($savePath, $allowHosts = [], $pageUrlPatterns = [])
    {
        $this->savePath = $savePath;
        $this->allowHosts = $allowHosts;
        $this->pageUrlPatterns = $pageUrlPatterns;
        $this->filesystem = new Filesystem();
    }

    protected function resolveAllowHosts($allowHosts)
    {
        $spider = $this->getSpider();
        $spider->setBlackUrlPatterns(array_map(function($host){
            return "#{$host}#";
        }, $allowHosts));
    }

    /**
     * @return array
     */
    public function getAllowHosts()
    {
        return $this->allowHosts;
    }

    /**
     * @return array
     */
    public function getPageUrlPatterns()
    {
        return $this->pageUrlPatterns;
    }

    public function getPageUrlDownloadTime($pageUrlPattern)
    {
        return isset($this->pageUrlPatternDownloadTimes[$pageUrlPattern]) ? $this->pageUrlPatternDownloadTimes[$pageUrlPattern] : 0;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        return $this->savePath;
    }

    /**
     * 生成文件名
     * @param Asset $asset
     * @return string
     */
    public function generateFileName(Asset $asset)
    {
        $basePath = rtrim($this->getSavePath() . DIRECTORY_SEPARATOR . $asset->getUrl()->getPath(), '\\/');
        $extension = $asset->getExtension();
        return $basePath . $this->getFilename($asset, $basePath) . ".{$extension}";
    }

    protected function getFilename(Asset $asset, $basePath)
    {
        $filename = pathinfo($asset->getUrl()->getUrlString(), PATHINFO_FILENAME);
        if (!$filename) {
            $unavailable = true;
            $index = 0;
            while ($unavailable) {
                $filename = "/index{$index}.html";
                if (!$this->filesystem->exists($basePath . $filename)) {
                    $unavailable = false;
                } else {
                    $index ++;
                }
            }
        }
        return $filename;
    }
}