<?php
    /**
     * Created by PhpStorm
     * @package Liujinyong\FastadminBin
     * User: Jack
     * Date: 2022/5/7
     * Time: 21:29
     */

    namespace Liujinyong\FastadminBin;

    use Liujinyong\FastadminBin\Commands\CreateForum;
    use Symfony\Component\Console\Application as Base;

    class Application extends Base
    {
        /**
         * Application constructor.
         *
         * @param string $name
         * @param string $version
         */
        public function __construct()
        {
            parent::__construct();
            $this->add(new CreateForum());
        }
    }