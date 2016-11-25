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
}