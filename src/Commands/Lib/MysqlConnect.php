<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\FastadminBin\Commands\Lib
     * User: Brahma
     * Date: 2022/6/27
     * Time: 18:49
     */

    namespace Liujinyong\FastadminBin\Commands\Lib;

    use Illuminate\Container\Container;
    use Illuminate\Database\Capsule\Manager;
    use Illuminate\Events\Dispatcher;
    use Symfony\Component\Console\Helper\Table;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\Question;


    class MysqlConnect
    {
        private static $object = null;
        private static $schema = null;


        private function __construct($input, $output, $handle)
        {
            //1.介绍
            $output->writeln("<info>【fastadmin-bin】是一个自由度较高的控制台命令</info>,<comment>以下是此命令的流程步骤</comment>");
            $table = new Table($output);
            $table->setHeaders(array('步骤', '事项'))->setRows(array(
                                                               array('[1.连接数据库]', '创建orm实例'),
                                                               array('[2.选择数据库]', '选择数据库及设置表前缀'),
                                                               array('[3.生成fastadmin控制器及目录]', '请填写路径'),
                                                               array('[4.生成fastadmin前台api]', '请填写路径'),
                                                           ));
            $table->render();
            $question = new Question("[1.connect database]，example(<fg=green> mysql -h127.0.0.1 -uroot -p123456 -P3306 </fg=green>):");
            $question->setValidator(function($value) use ($output) {
                if (trim($value) == '') {
                    throw new \Exception('数据库连接不能为空！');
                }
                $databaseInfo = explode(" ", $value);
                $host         = substr(trim($databaseInfo[1]), 2);
                $user         = substr(trim($databaseInfo[2]), 2);
                $password     = substr(trim($databaseInfo[3]), 2);
                $port         = isset($databaseInfo[4]) ? substr(trim($databaseInfo[4]), 2) : '3306';

                if ($host == "" || $user == "" || $password == "" || $port == "") {
                    throw new \Exception('参数有误');
                }
                if ((int)trim($port) < 0 || (int)trim($port) > 65535) {
                    throw new \Exception('数据库端口有误');
                }

                return [$host, $user, $password, $port];
            });

            $mysql = $handle->ask($input, $output, $question);
            $question->setMaxAttempts(3);
            $question = new Question("[2.choose database]，example(<fg=green>mysql -d`DatabaseName` -pre`TablePrefix`</fg=green>),(<fg=green>mysql -dceshi -prefa_</fg=green>):");
            $question->setValidator(function($value) use ($output) {
                if (trim($value) == '') {
                    throw new \Exception('选择数据库不能为空！');
                }
                $databaseInfo = explode(" ", $value);
                $database     = substr(trim($databaseInfo[1]), 2);
                $prefix       = isset($databaseInfo[2]) ? substr(trim($databaseInfo[2]), 4) : "";
                if ($database == "") {
                    throw new \Exception('数据库选择错误');
                }

                return [$database, $prefix];

            });
            $database = $handle->ask($input, $output, $question);
            $question->setMaxAttempts(3);
            $manager = new Manager();
            $manager->addConnection([
                                        'driver'    => 'mysql',
                                        'host'      => $mysql[0],
                                        'database'  => $database[0],
                                        'username'  => $mysql[1],
                                        'password'  => $mysql[2],
                                        'port'      => $mysql[3],
                                        'charset'   => 'utf8',
                                        'collation' => 'utf8_unicode_ci',
                                        'prefix'    => $database[1],
                                    ]);
            $manager->setEventDispatcher(new Dispatcher(new Container()));
            $manager->setAsGlobal();
            $manager->bootEloquent();
            self::$schema = Manager::schema();


        }

        public static function Instance(InputInterface $input, OutputInterface $output, $handle)
        {

            self::$object = self::$object ?? new self($input, $output, $handle);

            return self::$object;
        }

        public static function getSchema()
        {
            return self::$schema;
        }

        private function __clone()
        {

        }
    }