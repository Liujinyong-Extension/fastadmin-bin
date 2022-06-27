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

    class CreateBanner extends Command
    {

        public function __construct(string $name = null) { parent::__construct($name); }

        protected function configure()
        {
            $this->setName('banner:create')
                 ->setDescription('create forum')
                 ->addArgument('directory', InputArgument::OPTIONAL, 'Directory name for composer-driven project');
        }
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            //获取数据库实例
            $mysqlInstance = MysqlConnect::Instance($input,$output,$handler);


            if ( $mysqlInstance::getSchema()->hasTable("forum")) {
                $mysqlInstance::getSchema()->drop("forum");
            }
            $mysqlInstance::getSchema()->create("forum", function($table) {
                $table->increments('id')->comment("主键ID");
                $table->string('email')->unique()->comment("邮箱");
                $table->bigInteger('age')->comment("年龄");
                $table->enum('sex', ['0', '1'])->comment("0:男，1:女");
                $table->timestamp('create_time')->nullable()->comment("创建时间");
                $table->timestamp('update_time')->nullable()->comment("更新时间");
                $table->timestamp('delete_time')->nullable()->comment("删除时间");
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
            $execC= "php think crud -t forum -c ".$controllerMenu." --force=true";
            $execM= "php think menu -c ".$controllerMenu." --force=true";
            exec($execC);
            exec($execM);
            $output->writeln("<info>Admin-Controller&Menu Create Success!!!</info>");

            return 1;
        }
    }