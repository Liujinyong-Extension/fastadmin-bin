<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\FastadminBin\Commands
     * User: Jack
     * Date: 2022/5/7
     * Time: 17:33
     */

    namespace Liujinyong\FastadminBin\Commands;

    use Illuminate\Container\Container;
    use Illuminate\Database\Capsule\Manager;
    use Illuminate\Events\Dispatcher;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ConfirmationQuestion;
    use Symfony\Component\Console\Question\Question;

    class CreateForum extends Command
    {
        protected $databaseInfo = [
            'host'     => '',
            'port'     => '',
            'database' => '',
            'user'     => '',
            'password' => '',
            'prefix'   => '',
        ];

        public function __construct(string $name = null) { parent::__construct($name); }

        protected function configure()
        {
            $this->setName('forum:create')->setDescription('create forum')
                 ->addArgument('directory', InputArgument::OPTIONAL, 'Directory name for composer-driven project');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            //获取database信息
            $this->askDatabaseInfo($input, $output);
            //创建数据表
            $capsule = new Manager();
            $capsule->addConnection([
                                        'driver'    => 'mysql',
                                        'host'      => $this->databaseInfo['host'],
                                        'database'  => $this->databaseInfo['database'],
                                        'username'  => $this->databaseInfo['user'],
                                        'password'  => $this->databaseInfo['password'],
                                        'port'      => $this->databaseInfo['port'],
                                        'charset'   => 'utf8',
                                        'collation' => 'utf8_unicode_ci',
                                        'prefix'    => $this->databaseInfo['prefix'],
                                    ]);
            $capsule->setEventDispatcher(new Dispatcher(new Container()));
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            if (Manager::schema()->hasTable("forum")) {
                Manager::schema()->drop("forum");
            }
            Manager::schema()->create("forum", function($table) {
                $table->increments('id')->comment("主键ID");
                $table->string('email')->unique()->comment("邮箱");
                $table->bigInteger('age')->comment("年龄");
                $table->enum('sex', ['0', '1'])->comment("0:男，1:女");
                $table->timestamp('create_time')->nullable()->comment("创建时间");
                $table->timestamp('update_time')->nullable()->comment("更新时间");
                $table->timestamp('delete_time')->nullable()->comment("删除时间");
            });
            $output->writeln("table create success!!!");
            //自动生成后台数据
            $question = new ConfirmationQuestion("do you want create fastadmin view and api of forum<fg=yellow>(y/n)</fg=yellow>)???", true, '/^(y|yes)/i');
            if ($this->getHelperHandle()->ask($input, $output, $question)) {
                exec("php think curd -t forum -c forum/forum");
            }
            $output->writeln("\"php think curd -t forum -c forum/forum\" Success ");
            $question = new ConfirmationQuestion("do you want create fastadmin menu of forum  <fg=yellow>(y/n)</fg=yellow>)???", true, '/^(y|yes)/i');
            if ($this->getHelperHandle()->ask($input, $output, $question)) {
                exec("php think menu -c forum/forum");
            }
            $output->writeln("\"php think menu -c forum/forum\" Success ");

            return true;
        }

        protected function askDatabaseInfo($input, $output)
        {
            $helper   = $this->getHelperHandle();
            $question = new Question("host of your mysql(example: <fg=yellow>192.168.1.1</fg=yellow>):");
            $question->setValidator(function($value) {
                if (trim($value) == '') {
                    throw new \Exception('The database host can not be empty');
                }

                return $value;
            });
            $question->setMaxAttempts(3);
            $this->databaseInfo['host'] = $helper->ask($input, $output, $question);

            $question = new Question("port of your mysql(example: <fg=yellow>3306</fg=yellow>):");
            $question->setValidator(function($value) {
                if (trim($value) == '') {
                    throw new \Exception('The database port can not be empty');
                }
                if ((int)trim($value) < 0 || (int)trim($value) > 65535) {
                    throw new \Exception('The database port is wrong');
                }

                return $value;
            });
            $question->setMaxAttempts(3);
            $this->databaseInfo['port'] = $helper->ask($input, $output, $question);

            $question = new Question("database of your mysql(example: <fg=yellow>dbname</fg=yellow>):");
            $question->setValidator(function($value) {
                if (trim($value) == '') {
                    throw new \Exception('The database can not be empty');
                }

                return $value;
            });
            $question->setMaxAttempts(3);
            $this->databaseInfo['database'] = $helper->ask($input, $output, $question);


            $question = new Question("prefix of your database(example: <fg=yellow>fa_</fg=yellow>):");
            $question->setMaxAttempts(3);
            $this->databaseInfo['prefix'] = $helper->ask($input, $output, $question);

            $question = new Question("user of your mysql(example: <fg=yellow>root</fg=yellow>):");
            $question->setValidator(function($value) {
                if (trim($value) == '') {
                    throw new \Exception('The database user can not be empty');
                }

                return $value;
            });
            $question->setMaxAttempts(3);
            $this->databaseInfo['user'] = $helper->ask($input, $output, $question);

            $question = new Question("password of your mysql(example: <fg=yellow>123456</fg=yellow>):");
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $question->setValidator(function($value) {
                if (trim($value) == '') {
                    throw new \Exception('The database password can not be empty');
                }

                return $value;
            });
            $question->setMaxAttempts(3);
            $this->databaseInfo['password'] = $helper->ask($input, $output, $question);
        }

        protected function getHelperHandle()
        {
            return $this->getHelper("question");

        }

    }