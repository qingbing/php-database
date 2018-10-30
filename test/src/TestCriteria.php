<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-10-27
 * Version      :   1.0
 */

namespace Test;

use Db\Builder\Criteria;
use TestCore\Tester;


class TestCriteria extends Tester
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
        $db = \Db::getInstance();

        $criteria1 = (new Criteria())
            ->setSelect('c.name as courseName')
            ->addJoin("LEFT JOIN {{stu_course}} c ON(t.id=c.stu_id)");

        $criteria = (new Criteria())
            ->setTable('{{stu}}')
            ->setAlias('t')
            ->setSelect('t.id,t.name, t.sex')
            ->addWhere('t.id>:startId', [
                ':startId' => 2
            ])
            ->addWhereBetween('t.id', 3, 7)
            ->addCriteria($criteria1);

        $res = $db->findAll($criteria);
        var_dump($res);
    }
}