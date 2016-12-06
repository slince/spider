<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use GuzzleHttp\Psr7\Uri as Psr7Uri;
use Psr\Http\Message\UriInterface;

class Uri extends Psr7Uri
{
    /**
     * 参数
     * @var array
     */
    protected $parameters = [];

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param $name
     * @param $parameter
     */
    public function setParameter($name, $parameter)
    {
        $this->parameters[$name] = $parameter;
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function getParameter($name, $default = null)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public static function resolve(UriInterface $base, $rel)
    {
        $psr7Uri = parent::resolve($base, $rel);
        return static::createFromPsr7Uri($psr7Uri);
    }

    /**
     * 转换psr7 uri
     * @param Psr7Uri $uri
     * @return static
     */
    public static function createFromPsr7Uri(Psr7Uri $uri)
    {
        return new static($uri);
    }
}
