<?php
namespace Slince\Spider\Asset;

class Css extends Asset
{
    /**
     * 支持的mime type
     * @var array
     */
    static $supportedMimeTypes = ['text/css'];

    /**
     * 获取所有资源链接
     * @return array
     */
    function getAssetUrls()
    {
        return $this->handleRawUrls(array_merge(
            $this->extractCssUrls($this->content),
            $this->extractImageUrls($this->content)
        ));
    }
    /**
     * 从内容里提取所有的图片链接链接
     * 由于字体链接与图片链接处理方式相同，谷此处一并获取
     * @param $content
     * @return array
     * jpg|jpeg|gif|png|bmp|svg|ttf|eot|woff|otf|woff2
     */
    function extractImageUrls($content)
    {
        preg_match_all("/url\s*\((.*\.(?:jpg|jpeg|gif|png|bmp|svg|ttf|eot|woff|otf|woff2).*)\)/Ui", $content, $matches);
        $urls = empty($matches[1]) ? [] : $matches[1];
        array_walk($urls, function (&$url) {
            $url = trim($url, '"\'');
        });
        return $urls;
    }

    /**
     * 从内容里提取所有的样式链接
     * @param $content
     * @return array
     */
    function extractCssUrls($content)
    {
        preg_match_all("/url\s*\((.*\.css.*)\)/Ui", $content, $matches);
        $urls = empty($matches[1]) ? [] : $matches[1];
        array_walk($urls, function (&$url) {
            $url = trim($url, '"\'');
        });
        return $urls;
    }
}