<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-27
 * Version      :   1.0
 */

namespace Test;

use Components\Db;
use TestCore\Tester;


class TestSchema extends Tester
{
    /**
     * 执行函数
     * @return mixed|void
     * @throws \Exception
     */
    public function run()
    {
        /**
         * 获取db实例
         */
        $db = Db::getInstance([
            'c-file' => 'database',
            'c-group' => 'master',
        ]);

        // 获取表结构
        $schema = $db->getTable('{{stu}}');

        var_dump($schema);


    }
}