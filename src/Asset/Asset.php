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
        $this->uri = $uri;
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
    public function setUri(Uri $uri)
    {
        $this->uri = $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->uri;
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
    public function getPageUris()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetUris()
    {
        return [];
    }

    /**
     * 批量处理原生url
     * @param $rawUris
     * @return Uri[]
     */
    protected function handleRawUris($rawUris)
    {
        $rawUris = array_unique($rawUris);
        $uris = [];
        foreach ($rawUris as $rawUri) {
            if (!empty($rawUri) && $uri = $this->handleRawUri($rawUri)) {
                $uris[] = $uri;
            }
        }
        return $uris;
    }

    /**
     * 处理原生url
     * @param $rawUri
     * @return Uri|false
     */
    protected function handleRawUri($rawUri)
    {
        try {
            $uri = Uri::resolve($this->uri, $rawUri);
        } catch (\Exception $exception) {
            return false; //非法链接直接跳过
        }
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
