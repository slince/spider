<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Command;

use Slince\Config\Config;
use Slince\Event\Event;
use Slince\Spider\Factory;
use Slince\Spider\Spider;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class RunCommand extends Command
{
    /**
     * 命令名称
     * @var string
     */
    const COMMAND_NAME = 'run';
    
    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * @var Config
     */
    protected $configs;

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->configs = new Config();
    }

    public function configure()
    {
        $this->setName(static::COMMAND_NAME);
    }

    /**
     * 运行命令
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return true
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = getcwd() . '/spider.json';
        $this->configs->load($configFile);
        $spider = new Spider();
        $this->setSpider($spider);
        $this->prepare();
        $spider->run($this->configs->get('entrance'));
    }

    /**
     * Prepare before run
     */
    protected function prepare()
    {
        $handler = Factory::createHandler($this->configs['handler']['type'], $this->configs['handler']['config']);
        $this->getSpider()->pushHandler($handler);
//        $this->bindEventsForUi($this->getSpider(), $this->output);
    }

    /**
     * Bind Ui
     * @param Spider $spider
     * @param OutputInterface $output
     */
    protected function bindEventsForUi(Spider $spider, OutputInterface $output)
    {
        $spider->getDispatcher()->bind(Spider::EVENT_CAPTURE_URL, function (Event $event) use ($output) {
            $images = $event->getArgument('images');
            $progressBar = new ProgressBar($output, count($images));
            $output->writeln("Magic Hand started and will be performed {$progressBar->getMaxSteps()} images");
            $output->write(PHP_EOL);
            $progressBar->start();
            $this->progressBar = $progressBar;
        });

        $spider->getDispatcher()->bind(Spider::EVENT_PROCESS, function (Event $event) use ($output) {
            $this->progressBar->advance(1);
        });

        $spider->getDispatcher()->bind(Spider::EVENT_END, function (Event $event) use ($output) {
            $this->progressBar->finish();
            $output->writeln(PHP_EOL);
            $output->writeln("Work ok");
        });
    }
}
