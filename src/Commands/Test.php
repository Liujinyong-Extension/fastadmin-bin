<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\FastadminBin\Commands
     * User: Brahma
     * Date: 2022/5/10
     * Time: 21:14
     */

    namespace Liujinyong\FastadminBin\Commands;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class Test extends Command
    {
        protected function configure()
        {
            $this->setName('test')
                 ->setDescription('test commands')
                 ->addArgument('directory', InputArgument::OPTIONAL, 'Directory name for composer-driven project');
        }
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $output->writeln("this is test instance");
            return 0;
        }
    }