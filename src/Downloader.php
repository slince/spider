<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use GuzzleHttp\Client;
use Slince\Spider\Asset\Asset;

class Downloader
{

    /**
     * @var Client
     */
    protected $client;

    function __construct()
    {
        $this->client = $this->createHttpClient();
    }

    /**
     * @param Url $url
     * @return Asset
     */
    function download(Url $url)
    {
        $response = $this->client->get($url);
        $url->setParameter('response', $response);
        if ($response->getStatusCode() == '200') {
            $contentTypeString = $response->getHeaderLine('Content-type');
            $contentType = trim(strstr($contentTypeString, ';', true));
            return Asset::create($url, $response->getBody(), $contentType);
        }
        return false;
    }

    /**
     * 创建请求客户端
     * @return Client
     */
    protected function createHttpClient()
    {
        return new Client();
    }
}