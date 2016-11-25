<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Asset;

use Slince\Spider\Url;

class Asset implements AssetInterface
{
    /**
     * 支持的资源类型
     * @var array
     */
    static $supportedAsset = [
        'Slince\Spider\Asset\Html',
        'Slince\Spider\Asset\Image',
        'Slince\Spider\Asset\Css',
        'Slince\Spider\Asset\Script',
        'Slince\Spider\Asset\Pdf',
        'Slince\Spider\Asset\Text',
    ];

    /**
     * 默认资源
     * @var string
     */
    static $defaultAsset = 'Slince\Spider\Asset\Unknown';
    /**
     * 支持的mime type
     * @var array
     */
    static $supportedMimeTypes = ['*'];

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $contentType;

    function __construct(Url $url, $content, $contentType)
    {
        $this->setUrl($url);
        $this->setContentType($contentType);
        if (!empty($content)) {
            $this->setContent($content);
        }
    }

    /**
     * @param mixed $url
     */
    public function setUrl(Url $url)
    {
        $this->url = $url;
    }

    /**
     * @return Url
     */
    function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * 是否是二进制资源
     * @return bool
     */
    function isBinary()
    {
        return false;
    }

    /**
     * 批量处理原生url
     * @param $rawUrls
     * @return array
     */
    protected function handleRawUrls($rawUrls)
    {
        $rawUrls = array_unique($rawUrls);
        $urls = [];
        foreach ($rawUrls as $rawUrl) {
            if (!empty($rawUrl)) {
                $urls[] = $this->handleRawUrl($rawUrl);
            }
        }
        return $urls;
    }

    /**
     * 处理原生url
     * @param $rawUrl
     * @return Url
     */
    protected function handleRawUrl($rawUrl)
    {
        if (strpos($rawUrl, 'http') !== false || substr($rawUrl, 0, 2) == '//') {
            $newRawUrl = $rawUrl;
        } else {
            if ($rawUrl{0} !== '/') {
                if ($this->url->getParameter('extension') == '') {
                    $pathname = rtrim($this->url->getPath(), '/') . '/' . $rawUrl;
                } else {
                    $pathname = dirname($this->url->getPath()) . '/' . $rawUrl;
                }
            } else {
                $pathname = $rawUrl;
            }
            $newRawUrl = $this->url->getOrigin() . $pathname;;
        }
        $url = Url::createFromUrl($newRawUrl);
        $url->setRawUrl($rawUrl);
        //将链接所属的repository记录下来
        $url->setParameter('page', $this);
        return $url;
    }

    /**
     * 获取支持的文件类型
     * @return array
     */
    static function getSupportedMimeTypes()
    {
        return static::$supportedMimeTypes;
    }

    /**
     * 创建资源
     * @param Url $url
     * @param $content
     * @param $contentType
     * @return mixed
     */
    static function create(Url $url, $content, $contentType)
    {
        static $assetClasses = [];
        if (!isset($assetClasses[$contentType])) {
            foreach (self::$supportedAsset as $asset) {
                if (in_array($contentType, call_user_func([$asset, 'getSupportedMimeTypes']))) {
                    $assetClasses[$contentType] = $asset;
                }
            }
        }
        if (!isset($assetClasses[$contentType])) {
            $assetClass = self::$defaultAsset;
        } else {
            $assetClass = $assetClasses[$contentType];
        }
        return new $assetClass($url, $content, $contentType);
    }
}