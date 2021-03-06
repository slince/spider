<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Slince\Spider\Command\CollectCommand;
use Symfony\Component\Console\Application;

class CommandUI
{
    /**
     * 创建command
     * @return array
     */
    static function createCommands()
    {
        return [
            new CollectCommand(),
        ];
    }

    /**
     * command应用主入口
     * @throws \Exception
     */
    static function main()
    {
        $application = new Application();
        $application->addCommands(self::createCommands());
        $application->setAutoExit(true);
        $application->run();
    }
}