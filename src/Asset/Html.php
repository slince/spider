<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Asset;

use HtmlParser\ParserDom;

class Html extends Asset
{

    /**
     * @var ParserDom
     */
    protected static $domParser;

    /**
     * 页面标题
     * @var string
     */
    protected $title;

    /**
     * 页面关键词
     * @var string
     */
    protected $keywords;

    /**
     * 页面描述
     * @var string
     */
    protected $description;

    /**
     * 支持的mime type
     * @var array
     */
    static $supportedMimeTypes = [
        'text/html'
    ];

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        parent::setContent($content);
        self::getDomParser()->load($content);
        $titleDom = self::getDomParser()->find('title', 0);
        $keywordsDom = self::getDomParser()->find("meta[name=keywords]", 0);
        $descriptionDom = self::getDomParser()->find("meta[name=description]", 0);
        if ($titleDom !== false) {
            $this->title = $titleDom->getPlainText();
        }
        if ($keywordsDom !== false) {
            $this->keywords = $keywordsDom->getPlainText();
        }
        if ($descriptionDom !== false) {
            $this->description = $descriptionDom->getPlainText();
        }
    }

    /**
     * 获取所有资源链接
     * @return array
     */
    public function getAssetUrls()
    {
        return $this->handleRawUrls(array_merge(
            $this->extractCssUrls($this->content),
            $this->extractImageUrls($this->content),
            $this->extractScriptUrls($this->content),
            $this->extractPageUrls($this->content)
        ));
    }

    /**
     * 从内容里提取所有的页面链接
     * @param $content
     * @return array
     */
    protected function extractPageUrls($content)
    {
        self::getDomParser()->load($content);
        $aNodes = self::getDomParser()->find('a');
        return array_map(function($aNode){
            return $aNode->getAttr('href');
        }, $aNodes);
    }

    /**
     * 从内容里提取所有的图片链接链接
     * @param $content
     * @return array
     */
    protected function extractImageUrls($content)
    {
        self::getDomParser()->load($content);
        $imgNodes = self::getDomParser()->find('img');
        return array_map(function($imgNode){
            return $imgNode->getAttr('src');
        }, $imgNodes);
    }

    /**
     * 从内容里提取所有的样式链接
     * @param $content
     * @return array
     */
    protected function extractCssUrls($content)
    {
        self::getDomParser()->load($content);
        $cssNodes = self::getDomParser()->find("link[rel='stylesheet']");
        return array_map(function($cssNode){
            return $cssNode->getAttr('href');
        }, $cssNodes);
    }

    /**
     * 从内容里提取所有的脚本链接
     * @param $content
     * @return array
     */
    protected function extractScriptUrls($content)
    {
        self::getDomParser()->load($content);
        $scriptNodes = self::getDomParser()->find('script');
        return array_map(function($scriptNode){
            return $scriptNode->getAttr('src');
        }, $scriptNodes);
    }

    /**
     * @return ParserDom
     */
    protected static function getDomParser()
    {
        if (is_null(self::$domParser)) {
            self::$domParser = new ParserDom();
        }
        return self::$domParser;
    }
}