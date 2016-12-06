<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Processor\HtmlCollector;

use Slince\Spider\Asset\AssetInterface;
use Slince\Spider\Processor\Processor;
use Slince\Spider\Spider;
use Slince\Spider\Uri;
use Slince\Spider\Utility;
use Slince\Spider\Asset\Html;
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
        $this->filesystem = Utility::getFilesystem();
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
     * 检查链接是否继续
     * @param Uri $uri
     * @return boolean
     */
    public function checkUrlEnabled(Uri $uri)
    {
        if ($this->allowHosts) {
            $allowHostsPattern = $this->makeAllowHostsPattern($this->allowHosts);
            //如果当前链接host不符合允许的host则跳过
            if (!preg_match($allowHostsPattern, $uri->getHost())) {
                return false;
            }
        }
        //如果是重复页面正则并且已经下载过则不再下载
        if (!$this->checkPageUrlPatterns($uri)) {
            return false;
        }
        return true;
    }

    /**
     * 生成host验证规则
     * @param $allowHosts
     * @return string
     */
    protected function makeAllowHostsPattern($allowHosts)
    {
        return '#(' . implode('|', $allowHosts) . ')#';
    }

    /**
     * 获取某个页面正则已经  下载的次数
     * @param $pageUrlPattern
     * @return int|mixed
     */
    protected function getPageUrlDownloadTime($pageUrlPattern)
    {
        return isset($this->pageUrlPatternDownloadTimes[$pageUrlPattern]) ? $this->pageUrlPatternDownloadTimes[$pageUrlPattern] : 0;
    }

    /**
     * 检查是否符合页面正则
     * @param Uri $uri
     * @return bool
     */
    protected function checkPageUrlPatterns(Uri $uri)
    {
        $result = true;
        foreach ($this->pageUrlPatterns as $uriPattern => $template) {
            if (preg_match($uriPattern, $uri->getUrlString())) {
                //设置模式
                $uri->setParameter('pageUrlPattern', $uriPattern);
                if ($this->getPageUrlDownloadTime($uriPattern) > 1) {
                    $result = false;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * 持久化资源
     * @param AssetInterface $asset
     */
    public function saveAsset(AssetInterface $asset)
    {
        //静态资源所属父级repository的content要进行替换
        $parentAsset = $asset->getUrl()->getParameter('page');
        if (!is_null($parentAsset) && !$asset instanceof Html) {
            //调整父级内容
//            $parentAsset->setContent(preg_replace(
//                "#(?:http)?s?:?(?://)?{$asset->getUrl()->getHost()}#",
//                '',
//                $parentAsset->getContent()
//            ));
            $parentAsset->setContent(preg_replace(
                "#(?:http)?s?:?(?://)?{$asset->getUrl()->getHost()}#",
                '',
                $parentAsset->getContent()
            ));
        }
        $this->filesystem->dumpFile($this->generateFileName($asset), $asset->getContent());
        //如果有页面正则则维护页面信息
        if ($pageUrlPattern = $asset->getUrl()->getParameter('pageUrlPattern')) {
            if (!isset($this->pageUrlPatternDownloadTimes[$pageUrlPattern])) {
                $this->pageUrlPatternDownloadTimes[$pageUrlPattern] = 0;
            }
            $this->pageUrlPatternDownloadTimes[$pageUrlPattern] ++;
        }
    }

    /**
     * 生成文件名
     * @param AssetInterface $asset
     * @return string
     */
    protected function generateFileName(AssetInterface $asset)
    {
        $basePath = rtrim($this->savePath . dirname($asset->getUrl()->getPath()), '\\/') . DIRECTORY_SEPARATOR;
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
        return $filename . ".{$extension}";
    }
}