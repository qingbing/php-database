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

class TestBuilder extends Tester
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

        // 插入
        $res = $db->getInsertBuilder()
            ->setTable('{{stu}}')
            ->setColumns([
                'name' => 'name-builder-single',
                'sex' => 'builder',
            ])
            ->execute();
        var_dump($res);

        // 批量插入
        $res = $db->getInsertBuilder()
            ->setTable('{{stu}}')
            ->setMultiFields(['name', 'sex'])
            ->setMultiData([
                [
                    'name' => 'name-builder-multi_1',
                    'sex' => 'builder',
                ],
                [
                    'name' => 'name-builder-multi_2',
                    'sex' => 'builder',
                ],
            ])
            ->execute();
        var_dump($res);

        $lastId = $db->getLastInsertId();

        // 更新
        $res = $db->getUpdateBuilder()
            ->setTable('{{stu}}')
            ->setColumns([
                'sex' => 'update'
            ])
            ->addWhere('id=:id', [':id' => $lastId])
            ->execute();
        var_dump($res);

        // 删除
        $res = $db->getDeleteBuilder()
            ->setTable('{{stu}}')
            ->addWhere('id=:id', [':id' => 2])
            ->execute();
        var_dump($res);

        // 查询
        $res = $db->getFindBuilder()
            ->setTable('{{stu}}')
            ->setSelect(['id', 'name'])
            ->addWhere('id=:id', [':id' => $lastId])
            ->queryAll();
        var_dump($res);


    }
}