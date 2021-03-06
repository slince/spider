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
use Hoa\Mime\Exception\MimeIsNotFound;

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
     * @param Uri $uri
     * @param $content
     * @param $contentType
     * @param $extension
     * @return AssetInterface
     */
    public static function create(Uri $uri, $content, $contentType, $extension)
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
        return new $assetClass($uri, $content, $contentType, $extension);
    }

    /**
     * 从http响应创建资源
     * @param Response $response
     * @param Uri $uri
     * @return AssetInterface
     */
    public static function createFromPsr7Response(Response $response, Uri $uri)
    {
        list($contentType, $extension) = static::getAssetContentTypeAndExtension($uri, $response);
        return static::create($uri, $response->getBody()->getContents(), $contentType, $extension);
    }

    /**
     * 计算内容类型
     * @param Uri $uri
     * @param Response $response
     * @return string
     */
    protected static function getAssetContentTypeAndExtension(Uri $uri, Response $response)
    {
        $contentTypeString = $response->getHeaderLine('content-type');
        $contentType = strpos($contentTypeString, ';') === false
            ? trim($contentTypeString) : trim(explode(';', $contentTypeString)[0]);
        $extension = pathinfo($uri->getPath(), PATHINFO_EXTENSION);
        if (!$contentType) {
            if ($extension) {
                $contentType = Mime::getMimeFromExtension($extension);
            } else {
                $extension = 'html';
                $contentType = 'text/html';
            }
        } else {
            try {
                $extension = Mime::getExtensionsFromMime($contentType)[0];
            } catch (MimeIsNotFound $exception) {}
        }
        return [$contentType, $extension];
    }
}
