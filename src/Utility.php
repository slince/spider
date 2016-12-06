<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Symfony\Component\Filesystem\Filesystem;

final class Utility
{
    /**
     * 文件管理系统
     * @var Filesystem
     */
    protected static $filesystem;

    /**
     * 获取文件管理系统
     * @return Filesystem
     */
    public static function getFilesystem()
    {
        if (is_null(static::$filesystem)) {
            static::$filesystem = new Filesystem();
        }
        return self::$filesystem;
    }
}