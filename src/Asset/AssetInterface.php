<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Asset;

use Slince\Spider\Url;

interface AssetInterface
{
    /**
     * 获取资源url
     * @return Url
     */
    public function getUrl();

    /**
     * 资源内容
     * @return string
     */
    public function getContent();

    /**
     * 资源类型
     * @return string
     */
    public function getContentType();

    /**
     * 获取扩展名
     * @return string
     */
    public function getExtension();

    /**
     * 获取页面链接
     * @return Url[]
     */
    public function getPageUrls();

    /**
     * 获取子资源url
     * @return Url[]
     */
    public function getAssetUrls();

    /**
     * 是否是二进制资源
     * @return bool
     */
    public function isBinary();

    /**
     * 获取支持的资源类型
     * @return array
     */
    public static function getSupportedMimeTypes();
}
