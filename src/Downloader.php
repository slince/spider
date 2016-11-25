<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use GuzzleHttp\Client;
use Slince\Spider\Asset\AssetInterface;
use Slince\Spider\Exception\RuntimeException;

class Downloader
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = $this->createHttpClient();
    }

    /**
     * @param Url $url
     * @return AssetInterface
     */
    public function download(Url $url)
    {
        $response = $this->client->get($url);
        $url->setParameter('response', $response);
        if ($response->getStatusCode() == '200') {
            return AssetFactory::createFromPsr7Response($response, $url);
        }
        throw new RuntimeException("Download failed");
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