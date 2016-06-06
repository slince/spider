<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Resource;

use Slince\Spider\Url;

class Resource implements ResourceInterface
{
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

    function __construct(Url $url, $content)
    {
        $this->setUrl($url);
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
}