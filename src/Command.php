<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

use Slince\Config\Config;
use Slince\Event\Event;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Command extends BaseCommand
{
    /**
     * 命令名称
     * @var string
     */
    const COMMAND_NAME = 'go';
    
    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * @var Config
     */
    protected $configs;

    function initialize()
    {
        $this->configs = new Config();
    }

    function configure()
    {
        $this->setName(static::COMMAND_NAME);
    }

    /**
     * 运行命令
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return true
     */
    function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = getcwd() . '/spider.json';
        $this->configs->load($configFile);
        $spider = new Spider();
        $spider->go($this->configs->get('entrance'));
    }

    /**
     * 绑定ui
     * @param Spider $spider
     * @param OutputInterface $output
     */
    protected function bindEventsForUi(Spider $spider, OutputInterface $output)
    {
        $spider->getDispatcher()->bind(Spider::EVENT_CAPTURE_URL, function (Event $event) use ($output){
            $images = $event->getArgument('images');
            $progressBar = new ProgressBar($output, count($images));
            $output->writeln("Magic Hand started and will be performed {$progressBar->getMaxSteps()} images");
            $output->write(PHP_EOL);
            $progressBar->start();
            $this->progressBar = $progressBar;
        });

        $spider->getDispatcher()->bind(Spider::EVENT_PROCESS, function (Event $event) use ($output){
            $this->progressBar->advance(1);
        });

        $spider->getDispatcher()->bind(Spider::EVENT_END, function (Event $event) use ($output){
            $this->progressBar->finish();
            $output->writeln(PHP_EOL);
            $output->writeln("Work ok");
        });
    }
}