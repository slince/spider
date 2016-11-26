<?php
namespace Slince\Spider\Tests\Asset;

use Slince\Spider\Asset\Html;
use Slince\Spider\Url;

class HtmlTest extends \PHPUnit_Framework_TestCase
{
    protected function createAsset($content)
    {
        return new Html(new Url('http://www.domain.com/'), $content, 'text/html');
    }

    public function testParseAAndImgUrl()
    {
        $html = "<a href='http://www.domain.com/page1'><img src='http://www.domain.com/assets/img1.jpg'></a>";
        $asset = $this->createAsset($html);
        $this->assertEquals([
            'http://www.domain.com/page1',
            'http://www.domain.com/assets/img1.jpg'
        ], $asset->getAssetUrls());
    }

    public function testParseScriptAndStyleTagUrl()
    {
        $html = "<script src='http://www.domain.com/script1.js'></script><link href='http://www.domain.com/css1.css'>";
        $asset = $this->createAsset($html);
        $this->assertEquals([
            'http://www.domain.com/script1.js',
            'http://www.domain.com/css1.css'
        ], $asset->getAssetUrls());
    }
}