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
    use Symfony\Component\Console\Helper\ProgressBar;
    use Symfony\Component\Console\Helper\Table;
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
                exec("php think crud -t forum -c forum/forum");
            }
            $output->writeln("\"php think crud -t forum -c forum/forum\" Success ");
            $question = new ConfirmationQuestion("do you want create fastadmin menu of forum  <fg=yellow>(y/n)</fg=yellow>)???", true, '/^(y|yes)/i');
            if ($this->getHelperHandle()->ask($input, $output, $question)) {
                exec("php think menu -c forum/forum");
            }
            $output->writeln("\"php think menu -c forum/forum\" Success ");

            return 0;
        }

        protected function askDatabaseInfo($input, $output)
        {
            $helper   = $this->getHelperHandle();
            $output->writeln("<info>【fastadmin-bin】是一个自由度较高的控制台命令</info>,<comment>以下是此命令的流程步骤</comment>");
            $table = new Table($output);
            $table
                ->setHeaders(array('步骤', '事项'))
                ->setRows(array(
                              array('[1.连接数据库]', '为创建forum表前需创建orm实例'),
                              array('[2.是否自动生成后台api]', 'y/n'),
                              array('[3.是否自动生成后台目录]', 'y/n'),
                              array('[4.是否自动生成前台api]', 'y/n'),
                          ));
            $table->render();

            $question = new Question("[1.连接数据库]，例如(<fg=green>mysql -h127.0.0.1 -uroot -p123456 -P3306</fg=green>):");
            $question->setValidator(function($value)use ($output) {
                if (trim($value) == '') {
                    throw new \Exception('数据库连接不能为空！');
                }
                $databaseInfo = explode(" ",$value);
                $host = substr(trim($databaseInfo[1]),2);
                $user = substr(trim($databaseInfo[2]),2);
                $password = substr(trim($databaseInfo[3]),2);
                $port = substr(trim($databaseInfo[4]),2);
                if ($host == "" || $user == "" || $password =="" || $port == ""){
                    throw new \Exception('参数有误');
                }
                if ((int)trim($port) < 0 || (int)trim($port) > 65535) {
                    throw new \Exception('数据库端口有误');
                }
                return [$host,$user,$password,$port];
            });

            $database = $helper->ask($input, $output, $question);
            $question->setMaxAttempts(3);
            $this->databaseInfo['host'] = $database[0];
            $this->databaseInfo['user'] = $database[1];
            $this->databaseInfo['password'] = $database[2];
            $this->databaseInfo['port'] = $database[3];
            $question = new Question("[1.连接数据库]，例如(<fg=green>mysql -d`DatabaseName` -pre`TablePrefix`</fg=green>),例如(<fg=green>mysql -dceshi -prefa_</fg=green>):");
            $question->setValidator(function($value)use ($output) {
                if (trim($value) == '') {
                    throw new \Exception('选择数据库不能为空！');
                }
                $databaseInfo = explode(" ",$value);
                $database = substr(trim($databaseInfo[1]),2);
                $prefix = substr(trim($databaseInfo[2]),4);
                if ($database == ""){
                    throw new \Exception('数据库选择错误');
                }
                return [$database,$prefix];


            });
            $database = $helper->ask($input, $output, $question);
            $question->setMaxAttempts(3);
            $this->databaseInfo['database'] = $database[0];
            $this->databaseInfo['prefix'] = $database[1];

        }

        protected function getHelperHandle()
        {
            return $this->getHelper("question");

        }

    }