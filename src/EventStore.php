<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

class EventStore
{
    /**
     * 过滤url事件
     * @var string
     */
    const FILTERED_URL = 'filteredUrl';

    /**
     * 采集url内容事件
     * @var string
     */
    const CAPTURE_URL = 'captureUrl';

    /**
     * 采集完毕url内容事件
     * @var string
     */
    const CAPTURED_URL= 'capturedUrl';
}