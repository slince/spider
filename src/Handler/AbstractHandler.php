<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Handler;

use Slince\Event\Event;
use Slince\Spider\Spider;

abstract class AbstractHandler implements HandlerInterface
{
    function getEvents()
    {
        return [
            Spider::EVENT_CAPTURED_URL => 'process'
        ];
    }

    function process(Event $event){
        $resource = $event->getArgument('resource');
        call_user_func([$this, 'handle'], $resource);
    }
}