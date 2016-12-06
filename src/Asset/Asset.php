<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Asset;

use Hoa\Mime\Mime;
use Slince\Spider\Uri;

class Asset implements AssetInterface
{
    /**
     * 支持的mime type
     * @var array
     */
    protected static $supportedMimeTypes = ['*'];

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var string
     */
    protected $extension;

    public function __construct(Uri $uri, $content, $contentType, $extension = null)
    {
        $this->url = $uri;
        $this->contentType = $contentType;
        if (!empty($content)) {
            $this->setContent($content);
        }
        if (is_null($extension)) {
            $extension = Mime::getExtensionsFromMime($contentType);
        }
        $this->extension = $extension;
    }

    /**
     * @param mixed $uri
     */
    public function setUrl(Uri $uri)
    {
        $this->url = $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function isBinary()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageUrls()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetUrls()
    {
        return [];
    }

    /**
     * 批量处理原生url
     * @param $rawUrls
     * @return Uri[]
     */
    protected function handleRawUrls($rawUrls)
    {
        $rawUrls = array_unique($rawUrls);
        $uris = [];
        foreach ($rawUrls as $rawUrl) {
            if (!empty($rawUrl)) {
                $uris[] = $this->handleRawUrl($rawUrl);
            }
        }
        return $uris;
    }

    /**
     * 处理原生url
     * @param $rawUrl
     * @return Uri
     */
    protected function handleRawUrl($rawUrl)
    {
        // http://www.domain.com 或者 //www.domain.com
        if (strpos($rawUrl, 'http') !== false || substr($rawUrl, 0, 2) == '//') {
            $newRawUrl = $rawUrl;
        } else {
            if ($rawUrl{0} !== '/') {
                if (pathinfo($this->getUrl()->getPath(), PATHINFO_EXTENSION) == '') {
                    $pathname = rtrim($this->url->getPath(), '/') . '/' . $rawUrl;
                } else {
                    $pathname = dirname($this->url->getPath()) . '/' . $rawUrl;
                }
            } else {
                $pathname = $rawUrl;
            }
            $newRawUrl = $this->url->getOrigin() . $pathname;
        }
        $uri = Uri::createFromUrl($newRawUrl);
        $uri->setRawUrl($rawUrl);
        //将链接所属的repository记录下来
        $uri->setParameter('page', $this);
        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportedMimeTypes()
    {
        return static::$supportedMimeTypes;
    }
}
