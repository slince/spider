<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

class Url
{
    /**
     * http协议
     * @var string
     */
    const SCHEME_HTTP = 'http';

    /**
     * https协议
     * @var string
     */
    const SCHEME_HTTPS = 'https';

    /**
     * http协议默认端口
     * @var int
     */
    const PORT_HTTP = 80;

    /**
     * https协议默认端口
     * @var int
     */
    const PORT_HTTPS = 443;
    /**
     * 协议
     * @var string
     */
    protected $scheme;

    /**
     * 主机
     * @var string
     */
    protected $host;

    /**
     * 端口
     * @var string
     */
    protected $port;

    /**
     * 路径
     * @var string
     */
    protected $path;

    /**
     * 查询字符串
     * @var string
     */
    protected $query;

    /**
     * $ragment
     * @var string
     */
    protected $fragment;

    /**
     * 协议
     * @var string
     */
    protected $rawUrl;

    /**
     * 参数
     * @var array
     */
    protected $parameters = [];

    public function __construct($scheme = null, $host = null, $port = null, $path = null, $query = null, $fragment = null)
    {
        $this->setScheme($scheme);
        $this->setHost($host);
        $this->setPort($port);
        $this->setPath($path);
        $this->setQuery($query);
        $this->setFragment($fragment);
    }

    public function __toString()
    {
        return $this->getUrlString();
    }

    /**
     * 转换成字符串形式链接
     * @return string
     */
    public function getUrlString()
    {
        return function_exists('http_build_url') ? http_build_url([
            'scheme' => $this->scheme,
            'host' => $this->host,
            'port' => $this->port,
            'path' => $this->path,
            'query' => $this->query,
            'fragment' => $this->fragment,
        ]) : $this->buildUrl();
    }

    /**
     * 构建完整的url
     * @return string
     */
    protected function buildUrl()
    {
        $queryFragment = empty($this->query) ? '' : '?' . $this->query;
        $fragmentFragment = empty($this->fragment) ? '' : '#' . $this->fragment;
        return $this->getOrigin() . $this->path . $queryFragment . $fragmentFragment;
    }

    /**
     * 获取根域名
     * @return string
     */
    public function getOrigin()
    {
        $scheme = $this->getScheme();
        $schemeFragment = $scheme . '://';
        $hostFragment = (empty($this->port) || ($scheme == self::SCHEME_HTTP && $this->port == self::PORT_HTTP )
            || ($scheme == self::SCHEME_HTTPS && $this->port == self::PORT_HTTPS))
            ? $this->host : "{$this->host}:{$this->port}";
        return $schemeFragment . $hostFragment;
    }
    /**
     * 从url字符串创建
     * @param $url
     * @return static
     */
    public static function createFromUrl($url)
    {
        $fragments = parse_url($url);
        if ($fragments === false) {
            $fragments = array();
        }
        $scheme = isset($fragments['scheme']) ? $fragments['scheme']: '';
        $host = isset($fragments['host']) ? $fragments['host'] : '';
        $port = isset($fragments['port']) ? $fragments['port'] : '';
        $path = isset($fragments['path']) ? $fragments['path'] : '';
        $query = isset($fragments['query']) ? $fragments['query'] : '';
        $fragment = isset($fragments['fragment']) ? $fragments['fragment'] : '';
        $urlInstance = new static($scheme, $host, $port, $path, $query, $fragment);
        $urlInstance->setRawUrl($url);
        return $urlInstance;
    }
    
    /**
     * @param string $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme ?: self::SCHEME_HTTP;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param mixed $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
    }

    /**
     * @param string $rawUrl
     */
    public function setRawUrl($rawUrl)
    {
        $this->rawUrl = $rawUrl;
    }

    /**
     * @return string
     */
    public function getRawUrl()
    {
        return $this->rawUrl;
    }

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
}