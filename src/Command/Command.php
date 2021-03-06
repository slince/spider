<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Command;

use GuzzleHttp\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slince\Config\Config;
use Slince\Spider\Exception\InvalidArgumentException;
use Slince\Spider\Spider;
use Slince\Spider\TraceReport;
use Slince\Spider\Utility;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends BaseCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Spider
     */
    protected $spider;

    /**
     * @var Config
     */
    protected $configs;

    /**
     * @var Logger;
     */
    protected $logger;

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->spider = new Spider();
        $this->configs = new Config();
        $configFile = getcwd() . '/spider.json';
        if (file_exists($configFile)) {
            $this->configs->load($configFile);
            if (count($this->configs) == 0) {
                throw new InvalidArgumentException("Config File [{$configFile}] format error!");
            }
        }
        $this->resolveSpiderFilter();
        $this->prepareDownloader();
        $this->prepareLogger();
        //设置记录历史
        if ($input->getOption('record-history')) {
            //脚本结束记录已访问路径
            register_shutdown_function(function(){
                //记住已经访问的路径
                $filesystem = Utility::getFilesystem();
                $filesystem->dumpFile(getcwd() . DIRECTORY_SEPARATOR . md5(TraceReport::class), serialize(TraceReport::instance()));
            });
            //读取上次访问路径
            $this->readTraceReport();
        }
    }

    public function configure()
    {
        $this->addOption('record-history', null, InputOption::VALUE_OPTIONAL, 'Record spider crawl history', 0);
    }

    /**
     * 读取访问路径
     */
    protected function readTraceReport()
    {
        $filePath = getcwd(). DIRECTORY_SEPARATOR . md5(TraceReport::class);
        if (file_exists($filePath) && ($content = file_get_contents($filePath))) {
            $instance = unserialize($content);
            $instance instanceof TraceReport && TraceReport::setInstance($instance);
        }
    }

    /**
     * @return Spider
     */
    public function getSpider()
    {
        return $this->spider;
    }

    /**
     * @param Spider $spider
     */
    public function setSpider($spider)
    {
        $this->spider = $spider;
    }

    /**
     * 处理蜘蛛默认的过滤器
     */
    protected function resolveSpiderFilter()
    {
        if (isset($this->configs['filters']['url'])) {
            $filters = $this->configs['filters']['url'];
            if (isset($filters['whitUriPatterns'])) {
                $this->getSpider()->setWhiteUriPatterns($filters['whitUriPatterns']);
            }
            if (isset($filters['blackPatterns'])) {
                $this->getSpider()->setBlackUriPatterns($filters['blackPatterns']);
            }
        }
    }

    /**
     * 准备下载器设置
     */
    protected function prepareDownloader()
    {
        $options = isset($this->configs['httpClient']) ? $this->configs['httpClient'] : [];
        $this->spider->getDownloader()->setHttpClient(new Client($options));
    }

    /**
     * 准备日志
     */
    protected function prepareLogger()
    {
        $channel = isset($this->configs['log']['channel']) ? $this->configs['log']['channel'] : 'spider-collect';
        $savePath = isset($this->configs['log']['savePath']) ? $this->configs['log']['savePath'] : getcwd() . '/logs/';
        $logger = new Logger($channel, [
            new StreamHandler($savePath . 'error.log', Logger::ERROR),
        ]);
        $this->logger = $logger;
    }
}
