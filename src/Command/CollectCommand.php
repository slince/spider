<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Command;

use Slince\Spider\Event\CollectedUriEvent;
use Slince\Spider\Event\CollectUriEvent;
use Slince\Spider\Event\DownloadUriErrorEvent;
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
        $pageUriPatterns = isset($collectorConfigs['pageUriPatterns']) ? $collectorConfigs['pageUriPatterns'] : [];
        $this->htmlCollector = new HtmlCollector($this->getSpider(), $savePath, $allowHosts, $pageUriPatterns);
        $this->htmlCollector->mount(); //挂载到蜘蛛上
        $this->bindEventsForUi();
    }

    /**
     * Bind Ui
     */
    protected function bindEventsForUi()
    {
        $dispatcher = $this->getSpider()->getDispatcher();

        //开始处理某个链接
        $dispatcher->bind(EventStore::COLLECT_URL, function(CollectUriEvent $event){
            $uri = $event->getUri();
            $this->output->writeln(PHP_EOL);
            $this->output->writeln(strval($uri));
            $progressBar = new ProgressBar($this->output, 100);
            $progressBar->start();
            //临时存储该链接对应的进度条
            $uri->setParameter('progressBar', $progressBar);
        });

        //下载失败
        $dispatcher->bind(EventStore::DOWNLOAD_URL_ERROR, function (DownloadUriErrorEvent $event){
            $uri = $event->getUri();
            $this->output->writeln("Download Error");
        });

        //处理完成
        $dispatcher->bind(EventStore::COLLECTED_URL, function (CollectedUriEvent $event){
            $asset = $event->getAsset();
            $uri = $asset->getUri();
            $progressBar = $uri->getParameter('progressBar');
            $progressBar->advance(50);
            $progressBar->finish();
        });
    }
}
