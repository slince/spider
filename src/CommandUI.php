<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

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
            new Command(),
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
        $application->setDefaultCommand(Command::COMMAND_NAME);
        $application->run();
    }
}