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

    public function __construct(Url $url, $content, $contentType)
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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getContent()
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
    public function getContentType()
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
    public function isBinary()
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
    public static function getSupportedMimeTypes()
    {
        return static::$supportedMimeTypes;
    }
}