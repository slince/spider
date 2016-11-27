<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Processor;

use Slince\Spider\Spider;

abstract class Processor
{
    /**
     * @var Spider
     */
    protected $spider;

    /**
     * @param Spider $spider
     */
    public function setSpider($spider)
    {
        $this->spider = $spider;
    }

    /**
     * @return Spider
     */
    public function getSpider()
    {
        return $this->spider;
    }

    public function __construct(Spider $spider)
    {
        $this->spider = $spider;
    }

    /**
     * 挂载当前处理器
     * @return mixed
     */
    abstract public function mount();
}