<?php
/**
 * slince spider library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Spider;

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
    const COMMAND_NAME = 'thumbnail';
    
    /**
     * @var ProgressBar
     */
    protected $progressBar;

    function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->addOption('src', 's', InputOption::VALUE_OPTIONAL, 'The source image directory', getcwd() . '/src');
        $this->addOption('dst', 'd', InputOption::VALUE_OPTIONAL, 'The destination directory to save new images', getcwd());
    }

    /**
     * 运行命令
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return true
     */
    function execute(InputInterface $input, OutputInterface $output)
    {
        $src = $input->getOption('src');
        $dst = $input->getOption('dst');
        $questionHelper = new QuestionHelper();
        $size = $this->getSizeFromQuestion($questionHelper, $input, $output);
        $magicHand = new MagicHand($src, $dst, $size);
        $this->bindEventsForUi($magicHand, $output);
        $magicHand->run();
        return true;
    }

    /**
     * 询问得到高度和宽度
     * @param QuestionHelper $questionHelper
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function getSizeFromQuestion(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output)
    {
        $validator = function ($answer) {
            if (!is_numeric($answer)) {
                throw new \RuntimeException(
                    'Please input a valid number'
                );
            }
            return $answer;
        };
        //询问宽度
        $question = new Question("Image width you want: ", 50);
        $question->setValidator($validator);
        $width = $questionHelper->ask($input, $output, $question);
        //询问高度
        $question = new Question("Image height you want: ", 50);
        $question->setValidator($validator);
        $height = $questionHelper->ask($input, $output, $question);
        return [$width, $height];
    }

    /**
     * 绑定ui
     * @param MagicHand $magicHand
     * @param OutputInterface $output
     */
    protected function bindEventsForUi(MagicHand $magicHand, OutputInterface $output)
    {
        $magicHand->getDispatcher()->bind(MagicHand::EVENT_BEGIN, function (Event $event) use ($output){
            $images = $event->getArgument('images');
            $progressBar = new ProgressBar($output, count($images));
            $output->writeln("Magic Hand started and will be performed {$progressBar->getMaxSteps()} images");
            $output->write(PHP_EOL);
            $progressBar->start();
            $this->progressBar = $progressBar;
        });

        $magicHand->getDispatcher()->bind(MagicHand::EVENT_PROCESS, function (Event $event) use ($output){
            $this->progressBar->advance(1);
        });

        $magicHand->getDispatcher()->bind(MagicHand::EVENT_END, function (Event $event) use ($output){
            $this->progressBar->finish();
            $output->writeln(PHP_EOL);
            $output->writeln("Work ok");
        });
    }
}