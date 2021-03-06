<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-27
 * Version      :   1.0
 */

namespace Test;

use Components\Db;
use DBootstrap\Abstracts\Tester;
use DbSupports\Builder\Criteria;


class TestPagination extends Tester
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

        $criteria = (new Criteria())
            ->setTable('{{stu}}')
            ->addWhere('t.id>:startId', [
                ':startId' => 2
            ]);

        $res = $db->pagination($criteria, [], 2, 3);
        var_dump($res);


        var_dump(1111);
        $sql = "SELECT * FROM `{{stu}}` t WHERE t.id>:startId";
        $res = $db->pagination($sql, [
            ':startId' => 2
        ], 2);
        var_dump($res);

    }
}