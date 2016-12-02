<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider\Command;

use Slince\Config\Config;
use Slince\Spider\Exception\InvalidArgumentException;
use Slince\Spider\Spider;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
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
}
