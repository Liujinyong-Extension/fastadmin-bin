<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\FastadminBin\Commands
     * User: Brahma
     * Date: 2022/6/27
     * Time: 16:09
     */

    namespace Liujinyong\FastadminBin\Commands;


    use Liujinyong\FastadminBin\Commands\Lib\MysqlConnect;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\Question;
    use Symfony\Component\Filesystem\Filesystem;

    class DeleteBanner extends Command
    {
        protected $fs = null;

        public function __construct(string $name = null) { parent::__construct($name); }

        protected function configure()
        {
            $this->setName('banner:delete')->setDescription('delete forum')
                 ->addArgument('directory', InputArgument::OPTIONAL, 'Directory name for composer-driven project');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $handler  = $this->getHelper("question");
            $this->fs = new Filesystem();

            $mysqlInstance = MysqlConnect::Instance($input, $output, $handler);


            $question = new Question("[3.create admin-controller&menu],example(<fg=green>controller/menu</fg=green>),(<fg=green>test/demo</fg=green>):");
            $question->setValidator(function($value) {
                if (trim($value) == '') {
                    throw new \Exception('控制器层级不能为空');
                }

                return $value;

            });
            $controllerMenu = $handler->ask($input, $output, $question);
            $question->setMaxAttempts(3);
            if (strpos($controllerMenu, '/')) {
                $cmInfo      = explode('/', $controllerMenu);
                $adminpath1 = "application/admin/controller/" . $cmInfo[0] . '/' . ucfirst($cmInfo[1]) . ".php";
                $adminpath2 = "application/admin/model/" . $cmInfo[0] . '/' . ucfirst($cmInfo[1]) . ".php";
                $adminpath3 = "application/admin/validate/" . $cmInfo[0] . '/' . ucfirst($cmInfo[1]) . ".php";
                $adminpath4 = "application/admin/view/" . $cmInfo[0] . '/' . $cmInfo[1] . "/add.html";
                $adminpath5 = "application/admin/view/" . $cmInfo[0] . '/' . $cmInfo[1] . "/edit.html";
                $adminpath6 = "application/admin/view/" . $cmInfo[0] . '/' . $cmInfo[1] . "/index.html";
                $adminpath7 = "application/admin/view/" . $cmInfo[0] . '/' . $cmInfo[1] . "/recyclebin.html";
                $adminpath8 = "application/admin/lang/zh-cn/" . $cmInfo[0] . '/' . $cmInfo[1] . ".php";
                $adminpath9 = "public/assets/js/backend/" . $cmInfo[0] . '/' . $cmInfo[1] . ".js";
                $this->fs->remove($adminpath1);
                $this->fs->remove($adminpath2);
                $this->fs->remove($adminpath3);
                $this->fs->remove($adminpath4);
                $this->fs->remove($adminpath5);
                $this->fs->remove($adminpath6);
                $this->fs->remove($adminpath7);
                $this->fs->remove($adminpath8);
                $this->fs->remove($adminpath9);

                $output->writeln("<info>Admin-Controller&Menu&API Drop Success!!!</info>");

                $apiPathFile = "application/api/controller/" . $cmInfo[0] . '/' . ucfirst($cmInfo[1]) . ".php";
                $this->fs->remove($apiPathFile);
            } else {
                $cmInfo      = explode('/', $controllerMenu);
                $adminpath1 = "application/admin/controller/" . ucfirst($controllerMenu) . ".php";
                $adminpath2 = "application/admin/model/" . ucfirst($controllerMenu) . ".php";
                $adminpath3 = "application/admin/validate/" . ucfirst($controllerMenu) . ".php";
                $adminpath4 = "application/admin/view/" . $controllerMenu . "/add.html";
                $adminpath5 = "application/admin/view/" . $controllerMenu . "/edit.html";
                $adminpath6 = "application/admin/view/" . $controllerMenu . "/index.html";
                $adminpath7 = "application/admin/view/" . $controllerMenu . "/recyclebin.html";
                $adminpath8 = "application/admin/lang/zh-cn/" . $controllerMenu . ".php";
                $adminpath9 = "public/assets/js/backend/" . $controllerMenu . ".js";
                $this->fs->remove($adminpath1);
                $this->fs->remove($adminpath2);
                $this->fs->remove($adminpath3);
                $this->fs->remove($adminpath4);
                $this->fs->remove($adminpath5);
                $this->fs->remove($adminpath6);
                $this->fs->remove($adminpath7);
                $this->fs->remove($adminpath8);
                $this->fs->remove($adminpath9);
                $output->writeln("<info>Admin-Controller&Menu&API Drop Success!!!</info>");

                $apiPathFile = "application/api/controller/" . ucfirst($controllerMenu) . ".php";
                $this->fs->remove($apiPathFile);
            }
            exec("php think api --force=true");

            $output->writeln("<info>Admin-Api Drop Success!!!</info>");
            if ($mysqlInstance::getSchema()->hasTable("banner")) {
                $mysqlInstance::getSchema()->drop("banner");
                $output->writeln("<info>Table Drop success!!!</info>");
            }
            return 1;


        }
    }