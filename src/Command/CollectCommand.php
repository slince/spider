<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Command;

use Slince\Spider\Event\CollectedUrlEvent;
use Slince\Spider\Event\CollectUrlEvent;
use Slince\Spider\EventStore;
use Slince\Spider\Exception\InvalidArgumentException;
use Slince\Spider\Processor\HtmlCollector\HtmlCollector;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectCommand extends Command
{
    /**
     * 命令名称
     * @var string
     */
    const COMMAND_NAME = 'collect';
    
    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * html采集器
     * @var HtmlCollector
     */
    protected $htmlCollector;

    public function configure()
    {
        $this->setName(static::COMMAND_NAME)
            ->addArgument('url', InputArgument::OPTIONAL, 'Entrance url,collector will collect from this link');
    }

    /**
     * 运行命令
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return true
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepareCollect();
        $entrance = $this->configs->get('entrance') ?:  $input->getArgument('url');
        if (empty($entrance)) {
            throw new InvalidArgumentException("You should enter entrance url");
        }
        $this->getSpider()->run($entrance);
    }

    /**
     * Prepare before run
     */
    protected function prepareCollect()
    {
        $collectorConfigs = $this->configs->get('collector');
        $savePath = isset($collectorConfigs['savePath']) ? $collectorConfigs['savePath'] : getcwd();
        $allowHosts = isset($collectorConfigs['allowHosts']) ? $collectorConfigs['allowHosts'] : [];
        $pageUrlPatterns = isset($collectorConfigs['pageUrlPatterns']) ? $collectorConfigs['pageUrlPatterns'] : [];
        $this->htmlCollector = new HtmlCollector($this->getSpider(), $savePath, $allowHosts, $pageUrlPatterns);
        $this->bindEventsForUi();
    }

    /**
     * Bind Ui
     */
    protected function bindEventsForUi()
    {
        $dispatcher = $this->getSpider()->getDispatcher();
        //开始处理某个链接
        $dispatcher->bind(EventStore::COLLECT_URL, function(CollectUrlEvent $event){
            $url = $event->getUrl();
            $this->output->writeln(PHP_EOL);
            $this->output->writeln($url);
            $progressBar = new ProgressBar($this->output, 100);
            $progressBar->start();
            //临时存储该链接对应的进度条
            $url->setParameter('progressBar', $progressBar);
        });

        //处理完成
        $dispatcher->bind(EventStore::COLLECTED_URL, function (CollectedUrlEvent $event){
            $asset = $event->getAsset();
            $url = $asset->getUrl();
            $progressBar = $url->getParameter('progressBar');
            $progressBar->advance(50);
            $progressBar->finish();
        });
    }
}
