<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\FastadminBin\Commands
     * User: Brahma
     * Date: 2022/6/27
     * Time: 08:46
     */

    namespace Liujinyong\FastadminBin\Commands;

    use Liujinyong\FastadminBin\Commands\Lib\MysqlConnect;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\Question;
    use Symfony\Component\Filesystem\Filesystem;

    class CreateBanner extends Command
    {
        protected $fs = null;

        public function __construct(string $name = null) { parent::__construct($name); }

        protected function configure()
        {
            $this->setName('banner:create')->setDescription('create forum')
                 ->addArgument('directory', InputArgument::OPTIONAL, 'Directory name for composer-driven project');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            //获取数据库实例
            $handler  = $this->getHelper("question");
            $this->fs = new Filesystem();

            $mysqlInstance = MysqlConnect::Instance($input, $output, $handler);


            if ($mysqlInstance::getSchema()->hasTable("banner")) {
                $mysqlInstance::getSchema()->drop("banner");
            }
            $mysqlInstance::getSchema()->create("banner", function($table) {
                $table->increments('id')->comment("主键ID");
                $table->text('image')->comment("图片地址");
                $table->text('url')->nullable()->comment("链接");
                $table->integer('weight')->default(0)->comment("权重");
                $table->integer('create_time')->nullable()->comment("创建时间");
                $table->integer('update_time')->nullable()->comment("更新时间");
                $table->integer('delete_time')->nullable()->comment("删除时间");
            });
            $output->writeln("<info>Table Create success!!!</info>");

            $question = new Question("[3.create admin-controller&menu],example(<fg=green>controller/menu</fg=green>),(<fg=green>test/demo</fg=green>):");
            $question->setValidator(function($value) {
                if (trim($value) == '') {
                    throw new \Exception('控制器层级不能为空');
                }

                return $value;

            });
            $controllerMenu = $handler->ask($input, $output, $question);
            $question->setMaxAttempts(3);
            $execC = "php think crud -t banner -c " . $controllerMenu . " --force=true";
            $execM = "php think menu -c " . $controllerMenu . " --force=true";
            exec($execC);
            exec($execM);
            //复制修改后的文件到指定的位置 model
            if ($this->fs->exists( $fileModel = 'application/admin/model/' . $controllerMenu . '.php')) {

                $namespace = "app\\admin\\model;";
                $class = ucfirst($controllerMenu);
                if (strpos($controllerMenu, '/')) {

                    $namespace = "app\\admin\\model\\" . explode('/', $controllerMenu)[0] . ";";
                    $class = ucfirst(explode('/', $controllerMenu)[1]);

                }

                $content = str_replace(["{{namespace}}","{{class}}"], [$namespace,$class], file_get_contents(__DIR__ . '/../stubs/banner/banner.model.stub'));

                $this->fs->dumpFile($fileModel, $content);
            } else {
                $output->writeln("<info>model文件修改,复制失败</info>");

                return 1;
            }
            //复制修改后的文件到指定的位置 view
            $fileViewAdd  = 'application/admin/view/' . $controllerMenu . '/add.html';
            $fileViewEdit = 'application/admin/view/' . $controllerMenu . '/edit.html';

            if ($this->fs->exists($fileViewAdd) || $this->fs->exists($fileViewEdit)) {
                $this->fs->dumpFile($fileViewAdd, file_get_contents(__DIR__ . '/../stubs/banner/add.html.stub'));
                $this->fs->dumpFile($fileViewEdit, file_get_contents(__DIR__ . '/../stubs/banner/edit.html.stub'));
            } else {
                $output->writeln("<info>view文件修改,复制失败</info>");

                return 1;
            }

            //复制修改后的文件到指定的位置 js
            $fileJs = 'public/assets/js/backend/' . $controllerMenu . '.js';
            if ($this->fs->exists($fileJs)) {
                $content = str_replace("{{url}}", $controllerMenu, file_get_contents(__DIR__ . '/../stubs/banner/banner.js.stub'));

                $this->fs->dumpFile($fileJs, $content);
            } else {
                $output->writeln("<info>Js文件修改,复制失败</info>");

                return 1;
            }
            $output->writeln("<info>Admin-Controller&Menu&API Create Success!!!</info>");

            //if (!$this->fs->exists($apiPath)){

            $arr = [
                "app\\api\\controller;",
                "\\app\\admin\\model\\".ucfirst($controllerMenu),
                $controllerMenu,
                ucfirst($controllerMenu)
            ];
            if (strpos($controllerMenu, '/')) {
                $cmInfo      = explode('/', $controllerMenu);
                $arr[0]      = "app\\api\\controller\\" . $cmInfo[0] . ";";
                $arr[1]      = "\\app\\admin\\model\\" . $cmInfo[0] . "\\" . ucfirst($cmInfo[1]);
                $arr[2]      = $cmInfo[0] . "/" . $cmInfo[1];
                $arr[3]      = ucfirst($cmInfo[1]);
                $apiPath     = "application/api/controller/" . $cmInfo[0];
                $apiPathFile = $apiPath . '/' . ucfirst($cmInfo[1]) . ".php";
                $this->fs->mkdir($apiPath, 0755);
                $this->fs->touch($apiPathFile);

            } else {
                $apiPathFile = "application/api/controller/" . ucfirst($controllerMenu) . ".php";
                $this->fs->touch($apiPathFile);
            }
            $content = str_replace([
                                       "{{namespace}}",
                                       "{{bannerModel}}",
                                       "{{apiModel}}",
                                       "{{class}}"
                                   ], $arr, file_get_contents(__DIR__ . '/../stubs/banner/banner.api.stub'));
            $this->fs->dumpFile($apiPathFile, $content);
            exec("php think api --force=true");
            $output->writeln("<info>Admin-Api Create Success!!!</info>");

            return 1;
        }
    }