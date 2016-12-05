<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Processor\HtmlCollector;

use Slince\Spider\Asset\Asset;
use Slince\Spider\Asset\AssetInterface;
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

    /**
     * 页面正则下载次数
     * @var array
     */
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

    public function __construct(Spider $spider, $savePath, $allowHosts = [], $pageUrlPatterns = [])
    {
        parent::__construct($spider);
        $this->savePath = trim($savePath, '\/') . DIRECTORY_SEPARATOR;
        $this->allowHosts = $allowHosts;
        $this->pageUrlPatterns = $pageUrlPatterns;
        $this->filesystem = new Filesystem();
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function mount()
    {
        $this->getSpider()->getDispatcher()->addSubscriber(new Subscriber($this));
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

    /**
     * 获取某个页面正则已经  下载的次数
     * @param $pageUrlPattern
     * @return int|mixed
     */
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
     * @param AssetInterface $asset
     * @return string
     */
    public function generateFileName(AssetInterface $asset)
    {
        $basePath = rtrim($this->getSavePath() . dirname($asset->getUrl()->getPath()), '\\/') . DIRECTORY_SEPARATOR;
        $extension = $asset->getExtension();
        return $basePath . $this->getBasename($asset, $basePath);
    }

    /**
     * 获取基本文件名
     * @param AssetInterface $asset
     * @param $basePath
     * @return string
     */
    protected function getBasename(AssetInterface $asset, $basePath)
    {
        $extension = $asset->getExtension();
        //如果是符合正则页面的资源，使用配置的页面
        if ($pageUrlPattern = $asset->getUrl()->getParameter('pageUrlPattern')) {
            $filename = $this->pageUrlPatterns[$pageUrlPattern];
        } else {
            $filename = pathinfo($asset->getUrl()->getPath(), PATHINFO_FILENAME);
            if (!$filename) {
                $unavailable = true;
                $index = 0;
                while ($unavailable) {
                    $filename = "/index{$index}";
                    if (!$this->filesystem->exists($basePath . $filename . ".{$extension}")) {
                        $unavailable = false;
                    } else {
                        $index++;
                    }
                }
            }
        }
        return $filename  . ".{$extension}";
    }
}