<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Processor;

use Slince\Spider\Spider;

class Processor
{
    /**
     * @var Spider
     */
    protected $spider;

    /**
     * @return Spider
     */
    public function getSpider()
    {
        return $this->spider;
    }
}