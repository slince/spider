<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use GuzzleHttp\Psr7\Response;
use Slince\Spider\Asset\AssetInterface;
use Slince\Spider\Exception\InvalidArgumentException;
use Hoa\Mime\Mime;

class AssetFactory
{
    /**
     * 支持的资源类型
     * @var array
     */
    protected static $supportedAssetClass = [
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
    protected static $defaultAssetClass = 'Slince\Spider\Asset\Unknown';

    /**
     * 注册资源类
     * @param $assetClass
     */
    public static function registerAssetClass($assetClass)
    {
        if (!is_subclass_of($assetClass, 'Slince\Spider\Asset\AssetInterface')) {
            throw new InvalidArgumentException("Asset Class should implement [AssetInterface]");
        }
        static::$supportedAssetClass[] = $assetClass;
    }

    /**
     * 创建资源
     * @param Url $url
     * @param $content
     * @param $contentType
     * @return AssetInterface
     */
    public static function create(Url $url, $content, $contentType)
    {
        static $assetClasses = [];
        if (!isset($assetClasses[$contentType])) {
            foreach (static::$supportedAssetClass as $assetClass) {
                if (in_array($contentType, call_user_func([$assetClass, 'getSupportedMimeTypes']))) {
                    $assetClasses[$contentType] = $assetClass;
                }
            }
        }
        if (!isset($assetClasses[$contentType])) {
            $assetClass = static::$defaultAssetClass;
        } else {
            $assetClass = $assetClasses[$contentType];
        }
        return new $assetClass($url, $content, $contentType);
    }

    /**
     * 从http响应创建资源
     * @param Response $response
     * @param Url $url
     * @return AssetInterface
     */
    public static function createFromPsr7Response(Response $response, Url $url)
    {
        $contentType = static::getAssetContentType($url, $response);
        return static::create($url, $response->getBody(), $contentType);
    }

    /**
     * 计算内容类型
     * @param Url $url
     * @param Response $response
     * @return string
     */
    protected static function getAssetContentType(Url $url, Response $response)
    {
        $contentTypeString = $response->getHeaderLine('Content-type');
        $contentType = trim(strstr($contentTypeString, ';', true));
        if (!$contentType) {
            $contentType = Mime::getMimeFromExtension(pathinfo($url, PATHINFO_EXTENSION));
        }
        return $contentType;
    }
}