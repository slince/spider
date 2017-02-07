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
        parent::configure();
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
        return true;
    }

    /**
     * Prepare before run
     */
    protected function prepareCollect()
    {
        try {
            $collectorConfigs = $this->configs->get('collector');
            $savePath = isset($collectorConfigs['savePath']) ? $collectorConfigs['savePath'] : getcwd();
            $allowHosts = isset($collectorConfigs['allowHosts']) ? $collectorConfigs['allowHosts'] : [];
            $pageUriPatterns = isset($collectorConfigs['pageUriPatterns']) ? $collectorConfigs['pageUriPatterns'] : [];
            $autoAdjustLink = !empty($collectorConfigs['autoAdjustLink']); //修正链接
            $this->htmlCollector = new HtmlCollector($this->getSpider(), $savePath, $allowHosts, $pageUriPatterns, $autoAdjustLink);
            $this->htmlCollector->mount(); //挂载到蜘蛛上
            $this->bindEventsForUi();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Bind Ui
     */
    protected function bindEventsForUi()
    {
        $dispatcher = $this->getSpider()->getDispatcher();

        //开始处理某个链接
        $dispatcher->bind(EventStore::COLLECT_URI, function(CollectUriEvent $event){
            $uri = $event->getUri();
        });

        //下载失败
        $dispatcher->bind(EventStore::DOWNLOAD_URI_ERROR, function (DownloadUriErrorEvent $event){
            $uri = $event->getUri();
            $message = '[URL]' . strval($uri);
            if ($page = $uri->getParameter('page')) {
                $message .= " [Page]{$page->getUri()}";
            }
            $exception = $event->getArgument('exception');
            $message .= " [Exception]{$exception->getMessage()}";
            $this->logger->error($message);
            $this->output->writeln(strval($uri) . " error");
        });

        //处理完成
        $dispatcher->bind(EventStore::COLLECTED_URI, function (CollectedUriEvent $event){
            $asset = $event->getAsset();
            $uri = $asset->getUri();
            $this->output->writeln(strval($uri) . " ok");
        });
    }
}
